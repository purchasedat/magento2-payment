<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\API;

/**
 * Price information.
 *
 * @package PurchasedAt\API
 */
class Price
{

    /** @var number Gross price (parsed by json as string to preserver accuracy). */
    private $gross;
    /** @var string ISO4271 3 letter currency code. */
    private $currency;

    public static function fromJson($json)
    {
        $r = new Price();

        $r->setGross($json->gross);
        $r->setCurrency($json->currency);

        return $r;
    }

    /** @return number */
    public function getGross()
    {
        return $this->gross;
    }

    /**
     * @param number $gross
     * @return Price
     */
    public function setGross($gross)
    {
        $this->gross = $gross;
        return $this;
    }

    /** @return string */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Price
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

}
