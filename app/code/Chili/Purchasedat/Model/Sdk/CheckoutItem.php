<?php
/** This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at */
namespace Chili\Purchasedat\Model\Sdk {
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
            $this->count = $count;
            $this->name = $name;
            $this->sku = $sku;
            $this->price = $price;
            $this->externalId = $externalId;
        }
        public function build()
        {
            return array('count' => $this->count, 'name' => (object) $this->name, 'sku' => $this->sku, 'external_id' => $this->externalId, 'price' => (object) $this->price);
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
            $this->price = $price;
            return $this;
        }
        // builder
        /**
         * @param $count int
         * @param $sku string
         * @param null|string $lang
         * @param null|string $name
         * @param null|string $currency
         * @param null|string $price
         * @return CheckoutItem
         */
        public static function of($count, $sku, $lang = null, $name = null, $currency = null, $price = null)
        {
            $names = array();
            if ($lang !== null && $name === null || $lang === null && $name !== null) {
                throw new \InvalidArgumentException("if \$lang is given \$name must also be given and vice versa");
            } else {
                $names[$lang] = $name;
            }
            $prices = array();
            if ($currency !== null && $price === null || $currency === null && $price !== null) {
                throw new \InvalidArgumentException("if \$currency is given \$price must also be given and vice versa");
            } else {
                $prices[$currency] = $price;
            }
            return new CheckoutItem($count, $sku, $names, $prices);
        }
        /**
         * @param $lang string ISO2 language code
         * @param $name string name of item in specified language
         * @return $this
         */
        public function addName($lang, $name)
        {
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
            $this->price[$currency] = $price;
            return $this;
        }
    }
}