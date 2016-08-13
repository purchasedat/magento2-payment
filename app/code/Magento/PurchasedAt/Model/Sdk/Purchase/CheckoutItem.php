<?php
/** This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at */

namespace Magento\PurchasedAt\Model\Sdk\Purchase;

use Magento\PurchasedAt\Model\Sdk\Validation\Preconditions;
use Magento\PurchasedAt\Model\Sdk\Validation\Verify;

class CheckoutItem
{

    /** @var int Count of items sold. */
    private $count;
    /** @var array[string]string ISO2 language code => localized name. Name of this item in multiple languages */
    private $name;
    /** @var string Stock keeping unit (SKU) of this item. */
    private $sku;
    /** @var string External identifier of this item. */
    private $externalId;
    /** @var array[string]string Price of one unit of this item. */
    private $price;

    /**
     * CheckoutItem constructor.
     * @param int $count Count of items sold.
     * @param array $name ISO2 language code => localized name. Name of this item in multiple languages
     * @param string $sku Stock keeping unit (SKU) of this item.
     * @param array $price Price of one unit of this item.
     * @param string|null $externalId External identifier of this item (optional).
     */
    public function __construct($count, $sku, array $name = array(), array $price = array(), $externalId = null)
    {
        Preconditions::checkIntRange($count, 'count', 1);
        Preconditions::checkStringNonEmpty($sku, 'sku');

        $this->count = $count;
        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->externalId = $externalId;
    }

    public function build()
    {
        Verify::verifyIntRange($this->count, 'count', 1);
        Verify::verifyStringNonEmpty($this->sku, 'sku');
        Verify::verifyArrayNonEmpty($this->price, 'price');
        Verify::verifyArrayNonEmpty($this->name, 'name');
        
        return array(
            'count'       => $this->count,
            'name'        => (object)$this->name,
            'sku'         => $this->sku,
            'external_id' => $this->externalId,
            'price'       => (object)$this->price
        );
    }

    /** @return int */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count)
    {
        Preconditions::checkIntRange($count, 'count', 1);

        $this->count = $count;
        return $this;
    }

    /** @return array[string]string */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array [string]string $name
     * @return $this
     */
    public function setName(array $name)
    {
        Preconditions::checkDictionary($name, 'name', ['\\Magento\PurchasedAt\Model\Sdk\\Validation\\Preconditions','checkIso2Language'], ['\\Magento\PurchasedAt\Model\Sdk\\Validation\\Preconditions','checkStringNonEmpty']);

        $this->name = $name;
        return $this;
    }

    /** @return string */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        Preconditions::checkStringNonEmpty($sku, 'sku');

        $this->sku = $sku;
        return $this;
    }

    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     * @return $this
     */
    public function setExternalId($externalId)
    {
        if (!is_null($externalId)) {
            Preconditions::checkStringNonEmpty($externalId, 'externalId');
        }

        $this->externalId = $externalId;
        return $this;
    }

    /** @return array[string]string */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param array [string]string $price
     * @return $this
     */
    public function setPrice(array $price)
    {
        Preconditions::checkDictionary($price, 'price', ['\\Magento\PurchasedAt\Model\Sdk\\Validation\\Preconditions','checkIso3Currency'], ['\\Magento\PurchasedAt\Model\Sdk\\Validation\\Preconditions','checkFloat']);

        $this->price = $price;
        return $this;
    }

    // builder

    /**
     * @param $count int
     * @param $sku string
     * @return CheckoutItem
     */
    public static function of($count, $sku)
    {
        return new CheckoutItem($count, $sku, array(), array());
    }

    /**
     * @param $lang string ISO2 language code
     * @param $name string name of item in specified language
     * @return $this
     */
    public function addName($lang, $name)
    {
        Preconditions::checkIso2Language($lang, 'lang');
        Preconditions::checkStringNonEmpty($name, 'name');

        $this->name[$lang] = $name;
        return $this;
    }

    /**
     * @param $currency string ISO3 currency code
     * @param $price string price ofe item in specified currency
     * @return $this
     */
    public function addPrice($currency, $price)
    {
        Preconditions::checkIso3Currency($currency, 'currency');
        Preconditions::checkFloat($price, 'price');

        $this->price[$currency] = $price;
        return $this;
    }
}
