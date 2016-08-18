<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\API;

class Checkout
{

    /** @var \PurchasedAt\API\CheckoutItem[] Items sold in the checkout transaction. */
    private $items;
    /** @var \PurchasedAt\API\Price Total price of the checkout transaction */
    private $total;

    public static function fromJson($json)
    {
        $r = new Checkout();

        $r->setItems(
            array_map(function ($json) {
                return CheckoutItem::fromJson($json);
            }, $json->items)
        );
        $r->setTotal(Price::fromJson($json->total));

        return $r;
    }

    /**@return \PurchasedAt\API\CheckoutItem[] */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param \PurchasedAt\API\CheckoutItem[] $items
     * @return Checkout
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /** @return \PurchasedAt\API\Price */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param \PurchasedAt\API\Price $total
     * @return Checkout
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

}
