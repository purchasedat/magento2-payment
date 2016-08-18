<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\Purchase;

use PurchasedAt\Validation\Preconditions;

class Select
{

    /** @var string */
    private $item;
    /** @var string */
    private $itemSku;

    public function build()
    {
        if (!is_null($this->item) && !is_null($this->itemSku)) {
            throw new \LogicException('can only set one of item and itemSku');
        }

        if (!is_null($this->itemSku)) {
            return array(
                'item_sku' => $this->itemSku,
            );
        } else {
            return array(
                'item' => $this->item,
            );
        }
    }

    /** @return string */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param string $item Preselect the specified item in purchase dialog by its UUID.
     * @return Select
     */
    public function setItem($item)
    {
        if (!is_null($item)) {
            Preconditions::checkUUID($item, 'item');
        }

        $this->item = $item;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemSku()
    {
        return $this->itemSku;
    }

    /**
     * @param string $itemSku Preselect the specified item in purchase dialog by SKU.
     * @return Select
     */
    public function setItemSku($itemSku)
    {
        if (!is_null($itemSku)) {
            Preconditions::checkStringNonEmpty($itemSku, 'itemSku');
        }

        $this->itemSku = $itemSku;
        return $this;
    }


}
