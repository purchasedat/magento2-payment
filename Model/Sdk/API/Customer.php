<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\API;

/**
 * Customer information.
 *
 * @package PurchasedAt\API
 */
class Customer
{

    /**
     * @see \PurchasedAt\Purchase\Customer::email
     * @var string Email address of the customer
     */
    private $email;
    /**
     * @see \PurchasedAt\Purchase\Customer::externalId
     * @var string External id of the customer provided by the vendor (i.e. customer id in your database).
     */
    private $externalId;
    /** @var string Customer country detected by purchased.at. */
    private $country;
    /** @var string Customer language detected by purchased.at */
    private $language;

    public static function fromJson($json)
    {
        $r = new Customer();

        $r->setEmail($json->email);
        $r->setExternalId(isset($json->external_id) ? $json->external_id : null);
        $r->setCountry($json->country);
        $r->setLanguage($json->language);

        return $r;
    }

    /** @return string */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     * @return Customer
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }

    /** @return string */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Customer
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /** @return string */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return Customer
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

}
