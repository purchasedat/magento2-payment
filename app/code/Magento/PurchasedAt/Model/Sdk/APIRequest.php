<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk;

class APIRequest
{
    /** @var string protocol used to make the request */
    public $protocol;
    /** @var string the request method */
    public $method;
    /** @var string the requested path */
    public $path;
    /** @var string the IP address of the client executing the request */
    public $remoteAddress;
    /** @var array Associative array of HTTP headers (key = header name, value = header value). */
    public $headers;
    /** @var string HTTP body. */
    public $body;
    /** @var object HTTP body after json_decode. */
    public $jsonBody;
    /** @var string Value of the X-Pat-Signature HTTP header */
    public $signature;

    /**
     * APIResponse constructor.
     *
     * @param string $body
     * @param object $jsonBody
     * @param string $signature
     */
    public function __construct($body, $jsonBody, $signature)
    {
        $this->protocol = $_SERVER['SERVER_PROTOCOL'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']; // TODO checkme
        $this->remoteAddress = $_SERVER['REMOTE_ADDR'];
        $this->headers = APIRequest::getAllHeaders();
        $this->body = $body;
        $this->jsonBody = $jsonBody;
        $this->signature = $signature;
    }

    private static function getAllHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] =
                    $value;
            }
        }

        return $headers;
    }

}
