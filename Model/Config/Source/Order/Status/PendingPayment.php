<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PurchasedAt\Magento2Payment\Model\Config\Source\Order\Status;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Status source model
 */
class PendingPayment extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [Order::STATE_PROCESSING, Order::STATE_PENDING_PAYMENT, Order::STATE_PAYMENT_REVIEW, "pat_payment_failed", "pat_payment_reversed"];
}
