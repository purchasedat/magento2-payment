<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Chili\Purchasedat\Model\Sdk {

    class Checkout
    {
        /** @var array[string]string currency=>amount array of total price of items sold by this checkout. */
        private $total;
        /** @var CheckoutItem[] items sold in by this checkout. */
        private $items;

        public function build()
        {
            return array('total' => (object)$this->total, 'items' => array_map(function ($item) {
                return $item->build();
            }, $this->items));
        }

        /** @return array[string]string */
        public function getTotal()
        {
            return $this->total;
        }

        /**
         * @param array [string]string $total
         * @return $this
         */
        public function setTotal(array $total)
        {
            $this->total = $total;
            return $this;
        }

        /** @return CheckoutItem[] */
        public function getItems()
        {
            return $this->items;
        }

        /**
         * @param CheckoutItem[] $items
         * @return $this
         */
        public function setItems(array $items)
        {
            $this->items = $items;
            return $this;
        }
// build helper
        /**
         * Set the total value of all items (sum of all items: $item->count * $item->price).
         *
         * @param $currency string ISO3 currency code
         * @param $total string total value of all goods in $currency
         * @return $this
         */
        public function addTotal($currency, $total)
        {
            $this->total[$currency] = $total;
            return $this;
        }

        /**
         * Adds an item to the list of checkout items.
         *
         * @param $item CheckoutItem the item to add
         * @return $this
         */
        public function addItem($item)
        {
            $this->items[] = $item;
            return $this;
        }
    }
}