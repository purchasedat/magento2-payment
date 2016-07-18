<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk\API {
    class Item
    {
        /** @var string Server defined id of the item. (UUID) */
        private $id;
        /** @var string User defined Stock Keeping Unit (SKU) of the item. */
        private $sku;
        public static function fromJson($json)
        {
            $r = new Item();
            $r->setId($json->id);
            $r->setSku($json->sku);
            return $r;
        }
        /**@return string */
        public function getId()
        {
            return $this->id;
        }
        /**
         * @param string $id
         * @return Item
         */
        public function setId($id)
        {
            $this->id = $id;
            return $this;
        }
        /** @return string */
        public function getSku()
        {
            return $this->sku;
        }
        /**
         * @param string $sku
         * @return Item
         */
        public function setSku($sku)
        {
            $this->sku = $sku;
            return $this;
        }
    }
}