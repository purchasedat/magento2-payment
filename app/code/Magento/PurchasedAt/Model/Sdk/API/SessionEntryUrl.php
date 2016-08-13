<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk\API;

/**
 * Session entry point.
 * ADVANCED USERS ONLY.
 *
 * @package Magento\PurchasedAt\Model\Sdk\API
 */
class SessionEntryUrl
{

    /** @var string The entry URL. */
    private $entryUrl;

    public static function fromJson($json)
    {
        $r = new SessionEntryUrl();

        $r->setEntryUrl($json->entry_url);

        return $r;
    }

    /**
     * @return string
     */
    public function getEntryUrl()
    {
        return $this->entryUrl;
    }

    /**
     * @param string $entryUrl
     * @return $this
     */
    public function setEntryUrl($entryUrl)
    {
        $this->entryUrl = $entryUrl;
        return $this;
    }

}
