<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk\Purchase;

use Magento\PurchasedAt\Model\Sdk\Validation\Preconditions;
use Magento\PurchasedAt\Model\Sdk\Validation\Verify;

class Checkout
{
    /** @var array[string]string currency=>amount array of total price of items sold by this checkout. */
    private $total;
    /** @var CheckoutItem[] items sold in by this checkout. */
    private $items = [];

    public function build()
    {
        Verify::verifyArrayNonEmpty($this->items, 'items');
        Verify::verifyArrayNonEmpty($this->total, 'total');

        return array(
            'total' => (object)$this->total,
            'items' => array_map(
                function (CheckoutItem $item) {
                    return $item->build();
                },
                $this->items
            ),
        );
    }

    /** @return array[string]string */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param array[string]string $total
     * @return $this
     */
    public function setTotal(array $total)
    {
        Preconditions::checkDictionary($total, 'total', ['\\Magento\PurchasedAt\Model\Sdk\\Validation\\Preconditions','checkIso3Currency'], ['\\Magento\PurchasedAt\Model\Sdk\\Validation\\Preconditions','checkFloat']);

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
        Preconditions::checkArray($items, 'items', function ($v, $name) {
            Preconditions::checkSubclass($v, $name, '\\Magento\PurchasedAt\Model\Sdk\\Purchase\\CheckoutItem');
        });

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
        Preconditions::checkIso3Currency($currency, 'currency');
        Preconditions::checkFloat($total, 'total');

        $this->total[$currency] = $total;
        return $this;
    }

    /**
     * Adds an item to the list of checkout items.
     *
     * @param $item CheckoutItem the item to add
     * @return $this
     */
    public function addItem(CheckoutItem $item)
    {
        $this->items[] = $item;
        return $this;
    }

}
