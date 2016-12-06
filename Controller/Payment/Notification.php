<?php

namespace PurchasedAt\Magento2Payment\Controller\Payment;

use PurchasedAt\API;
use PurchasedAt\APIClient;


class Notification extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */

    /** @var \PurchasedAt\Magento2Payment\Model\PurchasedatModel */
    protected $_patModel;

    /**
     * Finish constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \PurchasedAt\Magento2Payment\Model\PurchasedatModel $patModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PurchasedAt\Magento2Payment\Model\PurchasedatModel $patModel
    ) {
        $this->_patModel = $patModel;
        parent::__construct($context);
       $this->logRequest();
    }

    /**
     * Load the page defined in view/frontend/layout/purchasedat_payment_finish.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('PurchasedAt\Magento2Payment\Helper\Data');
        $api_key = $helper->getConfig('payment/purchasedat/api_key');

        $apiClient = new APIClient($api_key);

        $result = $apiClient->parseTransactionNotificationForRequest();

        if(!$result->success) {
            error_log(sprintf('failed to process notification: %s',$result->errorCode));
            die('failed to handle request');
        }

        $notification = $result->result;
        $new_state = $notification->getNewState() ;
        $refund = false ;
        $parent_transaction_id = null ;
        $result = $apiClient->fetchTransaction($notification->getTransaction());

        $magento_transaction_id = $notification->getExternalTransactionId() ;
        $order_id = substr($magento_transaction_id, 0, strpos($magento_transaction_id, "-")) ;
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order_id);
        if ($new_state == "refund" || $new_state == "chargeback") {
            $refund = true ;
            $parent_transaction_id = $order->getPayment()->getLastTransId();
        }

        if ($order->getPayment() != null) {
            $this->_patModel->createMagentoTransaction($order, $result->result, $notification, $parent_transaction_id, $refund);
        }
        $apiClient->acknowledgeTransactionNotification();
    }

}