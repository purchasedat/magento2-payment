<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk\API {
    class CheckoutItem
    {
        /** @var int Count of items sol.d */
        private $count;
        /** @var string Name of the item as displayed to the customer. */
        private $name;
        /** @var string User defined Stock Keeping Unit (SKU) of the item. */
        private $sku;
        /** @var string External identifier of the item. */
        private $externalId;
        /** @var \PurchasedAt\API\Price Price of a single item. */
        private $price;
        /** @var \PurchasedAt\API\Price Calculated total price of a items (count * price). */
        private $total;
        public static function fromJson($json)
        {
            $r = new CheckoutItem();
            $r->setCount($json->count);
            $r->setName(isset($json->name) ? $json->name : null);
            $r->setSku($json->sku);
            $r->setExternalId(isset($json->external_id) ? $json->external_id : null);
            $r->setPrice(Price::fromJson($json->price));
            $r->setTotal(Price::fromJson($json->total));
            return $r;
        }
        /** @return int */
        public function getCount()
        {
            return $this->count;
        }
        /**
         * @param int $count
         * @return CheckoutItem
         */
        public function setCount($count)
        {
            $this->count = $count;
            return $this;
        }
        /** @return string */
        public function getName()
        {
            return $this->name;
        }
        /**
         * @param string $name
         * @return CheckoutItem
         */
        public function setName($name)
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
         * @return CheckoutItem
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
         * @return CheckoutItem
         */
        public function setExternalId($externalId)
        {
            $this->externalId = $externalId;
            return $this;
        }
        /** @return Price */
        public function getPrice()
        {
            return $this->price;
        }
        /**
         * @param Price $price
         * @return CheckoutItem
         */
        public function setPrice($price)
        {
            $this->price = $price;
            return $this;
        }
        /** @return Price */
        public function getTotal()
        {
            return $this->total;
        }
        /**
         * @param Price $total
         * @return CheckoutItem
         */
        public function setTotal($total)
        {
            $this->total = $total;
            return $this;
        }
    }
}