<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
    class Select
    {
        /** @var string */
        private $item;
        public function build()
        {
            return array('item' => $this->item);
        }
        /** @return string */
        public function getItem()
        {
            return $this->item;
        }
        /**
         * @param string $item Preselect the specified item in purchase dialog.
         * @return Select
         */
        public function setItem($item)
        {
            $this->item = $item;
            return $this;
        }
    }
}