<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk\API;

class Checkout
{

    /** @var \Magento\PurchasedAt\Model\Sdk\API\CheckoutItem[] Items sold in the checkout transaction. */
    private $items;
    /** @var \Magento\PurchasedAt\Model\Sdk\API\Price Total price of the checkout transaction */
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

    /**@return \Magento\PurchasedAt\Model\Sdk\API\CheckoutItem[] */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param \Magento\PurchasedAt\Model\Sdk\API\CheckoutItem[] $items
     * @return Checkout
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /** @return \Magento\PurchasedAt\Model\Sdk\API\Price */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param \Magento\PurchasedAt\Model\Sdk\API\Price $total
     * @return Checkout
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

}
