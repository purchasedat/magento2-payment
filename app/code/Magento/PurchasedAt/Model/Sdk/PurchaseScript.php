<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk;

use Magento\PurchasedAt\Model\Sdk\Signing\JWT;
use Magento\PurchasedAt\Model\Sdk\Signing\JWTOptions;

/**
 * This class creates a HTML embed code and thus helps with the first step of creating a purchase.
 *
 * Example:
 *
 * ```
 * $purchaseOptions = new Magento\PurchasedAt\Model\Sdk\PurchaseOptions($customerEmail);
 *
 * echo Magento\PurchasedAt\Model\Sdk\PurchaseScript::render($apiKey, $purchaseOptions);
 * ```
 */
class PurchaseScript
{

    /**
     * This function renders a HTML embed code required for creating a purchase.
     *
     * Example:
     *
     * ```
     * $purchaseOptions = new Magento\PurchasedAt\Model\Sdk\PurchaseOptions($customerEmail);
     *
     * echo Magento\PurchasedAt\Model\Sdk\PurchaseScript::render($apiKey, $purchaseOptions);
     * ```
     *
     * @param string          $apiKey          the api key
     * @param PurchaseOptions $purchaseOptions the purchase options
     * @param string          $target          id of target dom element
     * @param JWTOptions      $jwtOptions      the jwt options
     *
     * @return string HTML snippet to initialize the purchased.at widget
     *
     * @throws \InvalidArgumentException if an invalid type was provided to any of the parameters.
     */
    public static function render($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
    {
        $token = self::token($apiKey, $purchaseOptions, $jwtOptions);

        $widgetUrl = $purchaseOptions->getWidgetUrl();

        if (!isset($target) || !$target) {
            $target = null;
        }

        $params = json_encode(array('token' => $token));
        if (json_last_error()) {
            throw new \InvalidArgumentException(
                'The JWT options parameter to PurchaseScript needs to be JSON encodeable. '
                . json_last_error_msg());
        }
        $target = json_encode($target);
        if (json_last_error()) {
            throw new \InvalidArgumentException('The token parameter to PurchaseScript needs to be JSON encodeable. '
                . json_last_error_msg());
        }

        return '<script src="' . htmlspecialchars($widgetUrl) . '"></script>
                <script>
                        purchased_at.auto(' . $params . ',' . $target . ');
                </script>';
    }

    /**
     * This function generates the required JWT token to create a HTML embed code required for creating a purchase.
     *
     * Example:
     *
     * ```
     * $purchaseOptions = new Magento\PurchasedAt\Model\Sdk\PurchaseOptions($customerEmail);
     *
     * echo Magento\PurchasedAt\Model\Sdk\PurchaseScript::token($apiKey, $purchaseOptions);
     * ```
     *
     * @param string          $apiKey          the api key
     * @param PurchaseOptions $purchaseOptions the purchase options
     * @param JWTOptions      $jwtOptions      the jwt options
     *
     * @return string JWT token
     *
     * @throws \InvalidArgumentException if an invalid type was provided to any of the parameters.
     */
    public static function token($apiKey, $purchaseOptions, $jwtOptions = null) {
        if (!$purchaseOptions instanceof PurchaseOptions) {
            if (is_object($purchaseOptions)) {
                $type = get_class($purchaseOptions);
            } else {
                $type = gettype($purchaseOptions);
            }
            throw new \InvalidArgumentException('Invalid parameter type ' . $type .
                ' for purchaseOptions in PurchaseScript::render. ' .
                'Please use an instance of the PurchaseOptions class instead.');
        }
        if (!is_null($jwtOptions) && !$jwtOptions instanceof JWTOptions) {
            if (is_object($jwtOptions)) {
                $type = get_class($jwtOptions);
            } else {
                $type = gettype($jwtOptions);
            }
            throw new \InvalidArgumentException('Invalid parameter type ' . $type .
                ' for jwtOptions in PurchaseScript::render. Please use an instance of the JWTOptions class instead.');
        }

        $parts = explode(':', $apiKey);

        if (count($parts) != 2 || strlen($parts[0]) < 1 || strlen($parts[1]) < 1) {
            throw new \InvalidArgumentException(
                'Invalid API key (must contain exactly two parts separated by a colon)');
        }

        $key = $parts[1];
        $keyId = $parts[0];

        $utc = new \DateTimeZone('UTC');

        if (!isset($jwtOptions)) {
            $jwtOptions = new JWTOptions();
        }

        if (!$jwtOptions->getJwtId()) {
            $jwtOptions->setJwtId(base64_encode(mt_rand()));
        }

        if (!$jwtOptions->getNotBefore()) {
            $jwtOptions->setNotBefore((new \DateTime('now',
                $utc))->sub(new \DateInterval(Constants::DEFAULT_JWT_NOT_BEFORE_INTERVAL)));
        }

        if (!$jwtOptions->getExpiration()) {
            $jwtOptions->setExpiration(new \DateTime(Constants::DEFAULT_JWT_EXPIRATION, $utc));
        }


        $payload = $purchaseOptions->getPayload();

        if (!$payload->getCustomer()->getEmail()) {
            throw new \InvalidArgumentException(
                'The purchaseOptions parameter must contain the customers e-mail address. (Empty string found.)');
        }

        if (!$payload->getSdk()) {
            $payload->setSdk('PHP/' . Constants::SDK_VERSION);
        }

        return JWT::encode(array(
            'jti'         => $jwtOptions->getJwtId(),
            'aud'         => 'pat-api',
            'nbf'         => $jwtOptions->getNotBefore()->getTimestamp(),
            'exp'         => $jwtOptions->getExpiration()->getTimestamp(),
            'pat:payload' => $payload->build(),
        ), $key, $keyId);
    }

}
