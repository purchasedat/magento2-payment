<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk;

use Magento\PurchasedAt\Model\Sdk\API\RedirectData;
use Magento\PurchasedAt\Model\Sdk\Signing\JWTOptions;

/**
 * This class is main API client. However, before you start using this, please familiarize yourself with the process:
 *
 * 1. Embed the HTML button code to your checkout page. The easiest way is to use the `Magento\PurchasedAt\Model\Sdk\renderScript()`
 *    function. (Or `PurchaseScript::render()` if you prefer the OO style.)
 * 2. The customer clicks the button and completes the purchased.
 * 3. You receive a request/postback/notification about the purchase. Use APIClient::parseTransactionNotification() for
 *    this.
 * 4. The customer returns to your redirect page, where you process the return using APIClient::parseRedirect().
 */
class APIClient
{

    /**
     * URL of the server to use for API request.
     *
     * @var string
     */
    private $apiEndpoint;

    /**
     * If multiple $apiKeys are present, this defines the one used to make requests.
     *
     * @var string
     */
    private $apiKeyId;

    /**
     * The list of API keys known to this client.
     * Array-keys are the key ids and values the secrets.
     *
     * @var array
     */
    private $apiKeys = array();

    /**
     * If set to true, the signature of the responses of the server will be checked.
     *
     * @var boolean
     */
    private $verifySignature;

    /**
     * Maximum age of the response timestamp in milliseconds.
     * If <= -1 timestamp verification is disabled.
     * If you can't guarantee that your PHP server has a reasonably accurate time set you can disable the timestamp
     * verification.
     *
     * @var integer
     */

    private $verifyTimestampMaxAgeMillis;

    /**
     * APIClient constructor.
     *
     * @param string $apiKey the default API key to use with this client.
     */
    public function __construct($apiKey = null)
    {
        $this->apiEndpoint = Constants::DEFAULT_API_ENDPOINT;
        $this->verifySignature = Constants::DEFAULT_API_VERIFY_SIGNATURE;
        $this->verifyTimestampMaxAgeMillis = Constants::DEFAULT_API_VERIFY_TIMESTAMP_MAX_AGE;

        if (isset($apiKey)) {
            $this->apiKeyId = $this->addApiKey($apiKey);
        }
    }

    /**
     * @return RedirectData parsers the parameters sent by purchased.at for a redirect call.
     * @link https://docs.purchased.at/display/PUR/Payment+Widget#PaymentWidget-redirects
     */
    public function parseRedirect()
    {
        return RedirectData::fromRequest();
    }

    /**
     * Fetches information about a transaction by using the data available in a redirect.
     *
     * @see APIClient::fetchTransaction
     *
     * @param \Magento\PurchasedAt\Model\Sdk\API\RedirectData $redirectData redirect data to use, if null, attempts to parse redirect
     *                                                    data from request.
     *
     * @return APIResult {@link ApiResult::response} contains transaction information if the request was
     *                   successful.
     */
    public function fetchTransactionForRedirect($redirectData = null)
    {
        if ($redirectData === null) {
            $redirectData = $this->parseRedirect();
        }

        if ($redirectData->getTransactionId() === null) {
            return new APIResult(false, 'transaction_missing', null);
        }

        return $this->fetchTransaction($redirectData->getTransactionId());
    }

    /**
     * Fetches information about a transaction.
     *
     * @param string $transactionId server defined transaction id (UUID).
     *
     * @return APIResult {@link ApiResult::response} contains transaction information if the request was successful.
     */
    public function fetchTransaction($transactionId)
    {
        /** @var APIResult result */
        $result = $this->execCurl('/api/vendor/transaction/' . $transactionId, 'GET');
        if (!$result->success) {
            return $result;
        }

        $result->result = API\Transaction::fromJson($result->rawResponse->jsonBody);

        return $result;
    }

    /**
     * Parse an incoming transaction notification.
     *
     * @param string $requestBody     the request body
     * @param string $signatureHeader the value of the signature header
     *
     * @return APIResult the parsed transaction notification wrapped in an APIResult or error information
     */
    public function parseTransactionNotification($requestBody, $signatureHeader)
    {
        $json = json_decode($requestBody, false, 512, JSON_BIGINT_AS_STRING);
        $rawResponse = new APIRequest($requestBody, $json, $signatureHeader);

        $signatureResponse = $this->verifySignature($requestBody, $rawResponse);
        if ($signatureResponse !== null) {
            return $signatureResponse;
        }

        $timestampResponse = $this->verifyTimestamp($rawResponse);
        if ($timestampResponse !== null) {
            return $timestampResponse;
        }

        if ($json->type !== 'notification/transaction') {
            return new APIResult(false, 'body_invalid_type', $rawResponse);
        }

        $result = new APIResult(true, null, $rawResponse);
        $result->result = API\TransactionNotification::fromJson($json);

        return $result;
    }

    /**
     * Parse an incoming transaction notification and read the data from the request data.
     *
     * @see APIClient::parseTransactionNotification
     * @return APIResult the parsed transaction notification wrapped in an APIResult or error information
     */
    public function parseTransactionNotificationForRequest()
    {
        $requestBody = file_get_contents('php://input');
        $signatureHeader =
            array_key_exists('HTTP_X_PAT_SIGNATURE', $_SERVER) ? $_SERVER['HTTP_X_PAT_SIGNATURE'] : null;

        return $this->parseTransactionNotification($requestBody, $signatureHeader);
    }

    /**
     * Respond to the purchased.at server after receiving a transaction notification.
     *
     * @return string
     */
    public function acknowledgeTransactionNotification()
    {
        echo Constants::NOTIFICATION_OK_RESULT;
    }

    /**
     * Generate a session entry URL.
     * ADVANCED USERS ONLY.
     *
     * @param PurchaseOptions $purchaseOptions
     * @param JWTOptions $jwtOptions
     *
     * @return APIResult <a href='psi_element://ApiResult::response'>ApiResult::response</a> contains the session entry URL if the request was successful.
     */
    public function sessionEntryUrl(PurchaseOptions $purchaseOptions, JWTOptions $jwtOptions = null)
    {
        $apiKeyId = $this->assertApiKeyId();
        $apiKeySecret = $this->assertApiKeySecret($apiKeyId);

        $token = PurchaseScript::token($apiKeyId . ':' . $apiKeySecret, $purchaseOptions, $jwtOptions);
        $result = $this->execCurl('/api/vendor/session/entry-url', 'POST', json_encode(['token' => $token]));
        if (!$result->success) {
            return $result;
        }

        $result->result = API\SessionEntryUrl::fromJson($result->rawResponse->jsonBody);

        return $result;
    }

    /**
     * Add a new API key to this client.
     *
     * @param string $apiKey the API key to add
     *
     * @return string the key id part of the API key.
     * @throws \Exception when API key is wrong
     */
    public function addApiKey($apiKey)
    {
        if (strstr($apiKey, ':') === false) {
            throw new \Exception('invalid api key');
        }

        $apiKeyParts = explode(':', $apiKey);

        if (count($apiKeyParts) != 2 || strlen($apiKeyParts[0]) < 1 || strlen($apiKeyParts[1]) < 1) {
            throw new \Exception('invalid api key');
        }

        $this->apiKeys[$apiKeyParts[0]] = $apiKeyParts[1];

        return $apiKeyParts[0];
    }

    /**
     * @param string $path
     * @param string $method
     * @param object|null $requestBody
     * @return null|APIResult
     */
    private function execCurl($path, $method = 'GET', $requestBody = null)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $apiKeyId = $this->assertApiKeyId();
        $apiKeySecret = $this->assertApiKeySecret($apiKeyId);

        $signature = null;
        {
            $signatureLines = [$path];
            if (!is_null($requestBody)) {
                $signatureLines [] = $requestBody;
            }
            $signature = hash_hmac('sha512', implode("\n", $signatureLines), $apiKeySecret);
        }

        $requestHeaders = [
            'X-Pat-Authorization: ' . $apiKeyId . ':' . $signature,
            'X-Pat-SDK: PHP/' . Constants::SDK_VERSION,
        ];

        switch ($method) {
            case 'GET':
                if (!is_null($requestBody)) {
                    throw new \InvalidArgumentException('cannot have body with method GET');
                }
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
                $requestHeaders []= 'Content-Type: application/json;charset=utf-8';
                break;
            default:
                throw new \InvalidArgumentException('unsupported method ' . $method);
        }

        // the authorization header must be set so purchased.at can verify that the request came from an authorized party
        curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);

        $response = curl_exec($curl);

        if ($response === false) {
            $errorNumber = curl_errno($curl);

            return new APIResult(false, 'curl_error_' . $errorNumber, null);
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // class preloader workaround
        $newLine = chr(0x0D) . chr(0x0A);

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $headers = substr($headers, strpos($headers, $newLine) + 2);
        $body = substr($response, $headerSize);

        curl_close($curl);

        $headers_ = array();
        foreach (explode($newLine, $headers) as $header) {
            $headerParts = explode(': ', $header, 2);
            $name = $headerParts[0];
            $value = isset($headerParts[1]) ? $headerParts[1] : null;
            if (!isset($name) || $name == '') {
                continue;
            }
            $headers_[$name] = $value;
        }
        $headers = $headers_;
        $signature = $headers['X-Pat-Signature'];

        $jsonBody = json_decode($body, false, 512, JSON_BIGINT_AS_STRING);

        $rawResponse = new APIResponse($status, $headers, $body, $jsonBody, $signature);

        $signatureResponse = $this->verifySignature($body, $rawResponse);
        if ($signatureResponse !== null) {
            return $signatureResponse;
        }

        $timestampResponse = $this->verifyTimestamp($rawResponse);
        if ($timestampResponse !== null) {
            return $timestampResponse;
        }

        if ($rawResponse->status / 100 != 2) {
            return new APIResult(false, APIClient::errorCodeForStatus($rawResponse->status), $rawResponse);
        }

        return new APIResult(true, null, $rawResponse);
    }

    /**
     * @param string $signatureHeader
     *
     * @return array
     */
    private function parseSignatureHeader($signatureHeader)
    {
        $signatureParts = explode(':', $signatureHeader, 2);
        $keyId = $signatureParts[0];
        $signature = $signatureParts[1];
        $secret = $this->apiKeys[$keyId];

        return array($keyId, $signature, $secret);
    }

    /**
     * @param string                 $signatureData
     * @param APIRequest|APIResponse $rawResponse
     *
     * @return null|APIResult
     */
    private function verifySignature($signatureData, $rawResponse)
    {
        if (!$this->verifySignature) {
            return null;
        }

        if (!isset($rawResponse->signature) || $rawResponse->signature == '') {
            return new APIResult(false, 'signature_header_missing', $rawResponse);
        }

        if (strpos($rawResponse->signature, ':') === false) {
            return new APIResult(false, 'signature_header_invalid', $rawResponse);
        }

        list($keyId, $signature, $usedKey) = $this->parseSignatureHeader($rawResponse->signature);

        if ($keyId == null || $signature == null || $usedKey == null) {
            return new APIResult(false, 'signature_key_invalid', $rawResponse);
        }

        $computedSignature = hash_hmac('sha512', $signatureData, $usedKey);

        if ($computedSignature != $signature) {
            return new APIResult(false, 'signature_invalid', $rawResponse);
        }

        return null;
    }

    /**
     * @param APIRequest|APIResponse $rawResponse
     *
     * @return null|APIResult
     */
    private function verifyTimestamp($rawResponse)
    {
        if ($this->verifyTimestampMaxAgeMillis <= -1) {
            return null;
        }

        if ($rawResponse->jsonBody == null) {
            return new APIResult(false, 'body_not_json', $rawResponse);
        }

        $timestamp = $rawResponse->jsonBody->timestamp;

        if (!isset($timestamp) || $timestamp == null) {
            return new APIResult(false, 'body_no_timestamp', $rawResponse);
        }

        $now = time(); // time() is UTC and server always returns UTC as well
        $timestampDifference = $now - $timestamp;

        if (abs($timestampDifference) > $this->verifyTimestampMaxAgeMillis) {
            return new APIResult(false, 'timestamp_out_of_range', $rawResponse);
        }

        return null;
    }

    private function assertApiKeyId()
    {
        if (!isset($this->apiKeyId)) {
            throw new \Exception('api key missing');
        }

        return $this->apiKeyId;
    }

    /**
     * @param string $apiKeyId
     *
     * @return string
     * @throws \Exception
     */
    private function assertApiKeySecret($apiKeyId)
    {
        $apiKeySecret = $this->apiKeys[$apiKeyId];
        if (!isset($apiKeySecret)) {
            throw new \Exception('api key secret missing');
        }

        return $apiKeySecret;
    }

    /**
     * @param integer $status
     *
     * @return string
     */
    private static function errorCodeForStatus($status)
    {
        switch ($status) {
            case 401:
                return 'unauthorized';
            case 403:
                return 'forbidden';
            case 404:
                return 'not_found';
        }

        if ($status / 100 == 5) {
            return 'server_error';
        }

        return 'unknown_error';
    }

    /** @return string */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }

    /**
     * @param string $apiEndpoint
     * @return APIClient
     */
    public function setApiEndpoint($apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
        return $this;
    }

    /** @return string */
    public function getApiKeyId()
    {
        return $this->apiKeyId;
    }

    /**
     * @param string $apiKeyId
     * @return APIClient
     */
    public function setApiKeyId($apiKeyId)
    {
        $this->apiKeyId = $apiKeyId;
        return $this;
    }

    /** @return array */
    public function getApiKeys()
    {
        return $this->apiKeys;
    }

    /**
     * @param array $apiKeys
     * @return APIClient
     */
    public function setApiKeys($apiKeys)
    {
        $this->apiKeys = $apiKeys;
        return $this;
    }

    /**
     * @param boolean $verifySignature
     * @return APIClient
     */
    public function setVerifySignature($verifySignature)
    {
        $this->verifySignature = $verifySignature;
        return $this;
    }

    /**
     * @param int $verifyTimestampMaxAgeMillis
     * @return APIClient
     */
    public function setVerifyTimestampMaxAgeMillis($verifyTimestampMaxAgeMillis)
    {
        $this->verifyTimestampMaxAgeMillis = $verifyTimestampMaxAgeMillis;
        return $this;
    }

}
