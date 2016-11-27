<?php

namespace PurchasedAt\Magento2Payment\Controller\Payment;

use PurchasedAt\API;


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
//        $this->logRequest();
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

        $apiClient = new Sdk\APIClient($api_key);

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

    protected function logRequest(){
    	error_reporting(E_ERROR);
    	ini_set("display_errors", 1);

    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		/**
		 * @var \Magento\Framework\App\Request\Http $req
		 */
    	$request = $objectManager->get('\Magento\Framework\App\Request\Http');
    	$requestBody = $request->getContent();

    	$data = sprintf(
    		"%s %s %s\n",
    		$_SERVER['REQUEST_METHOD'],
    		$_SERVER['REQUEST_URI'],
    		$_SERVER['SERVER_PROTOCOL']
    	);

    	$headerList = [];
    	foreach ($_SERVER as $name => $value) {
    		if (preg_match('/^HTTP_/',$name)) {
    			// convert HTTP_HEADER_NAME to Header-Name
    			$name = strtr(substr($name,5),'_',' ');
    			$name = ucwords(strtolower($name));
    			$name = strtr($name,' ','-');
    			// add to list
    			$headerList[$name] = $value;
    		}
    	}

    	foreach ($headerList as $name => $value) {
    		$data .= $name . ': ' . $value . "\n";
    	}
    	$data .= "\n" . $requestBody . "\n";

    	$targetFile = getcwd() . DIRECTORY_SEPARATOR . "var"  . DIRECTORY_SEPARATOR . "log"  . DIRECTORY_SEPARATOR .  "http_req_" . date("Y-m-d_H:i:s") . "-" . substr(md5($data . time()), 0, 8) . ".log";

    	file_put_contents(
    		$targetFile,
    		$data
    	);

    }

}