<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt;

class APIResult
{

    /** @var bool True of request was successful, false otherwise. */
    public $success;

    /** @var string HTTP error code if request wasn't successful. */
    public $errorCode;

    /** @var APIResponse Raw response as extracted from curl call. */
    public $rawResponse;

    /** @var mixed The parsed response if the request was successful. */
    public $result;

    /**
     * APIResult constructor.
     *
     * @param bool                   $success
     * @param string                 $errorCode
     * @param APIResponse|APIRequest $rawResponse
     */
    public function __construct($success, $errorCode, $rawResponse)
    {
        $this->success = $success;
        $this->errorCode = $errorCode;
        $this->rawResponse = $rawResponse;
    }

}
