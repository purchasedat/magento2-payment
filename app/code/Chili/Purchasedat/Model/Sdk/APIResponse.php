<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
    class APIResponse
    {
        /** @var int HTTP status of the response. */
        public $status;
        /** @var array Associative array of HTTP headers (key = header name, value = header value). */
        public $headers;
        /** @var string HTTP body. */
        public $body;
        /** @var object HTTP body after json_decode. */
        public $jsonBody;
        /** @var string Value of the X-Pat-Signature HTTP header. */
        public $signature;
        /**
         * APIResponse constructor.
         *
         * @param int    $status
         * @param array  $headers
         * @param string $body
         * @param object $jsonBody
         * @param string $signature
         */
        public function __construct($status, $headers, $body, $jsonBody, $signature)
        {
            $this->status = $status;
            $this->headers = $headers;
            $this->body = $body;
            $this->jsonBody = $jsonBody;
            $this->signature = $signature;
        }
    }
}