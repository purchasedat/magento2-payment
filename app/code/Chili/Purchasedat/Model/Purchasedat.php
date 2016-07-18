<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chili\Purchasedat\Model;

use Chili\Purchasedat\Model\Sdk\API;
use Chili\Purchasedat\Model\Sdk;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\App;


/**
 * Pay In Store payment method model
 */
class Purchasedat extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'purchasedat';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canAuthorize            = true;

    /**
     * @var bool
     */
    protected $_canCapture              = true;

    /**
     * @var bool
     */
    protected $_canRefund               = true;

    /**
     * @var bool
     */
    protected $_canUseInternal          = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout          = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var array|null
     */
    protected $requestMaskedFields      = null;


    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var TransactionCollectionFactory
     */
    protected $salesTransactionCollectionFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Test mode or live
     *
     * @var string
     */
    protected $_test = 'test';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param TransactionCollectionFactory $salesTransactionCollectionFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetaData
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
//        \Chili\PurchasedAt\Purchase\CheckoutItem $checkoutItem = null,
//        \Chili\PurchasedAt\PurchaseOptions $purchaseOptions = null,
        array $data = []){
        $this->orderFactory = $orderFactory;
        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
//            $checkoutItem,
//            $purchaseOptions,
            $data);
    }

    //@param \Magento\Framework\Object|\Magento\Payment\Model\InfoInterface $payment
    public function getAmount($orderId)//\Magento\Framework\Object $payment)
    {   //\Magento\Sales\Model\OrderFactory
        $orderFactory=$this->orderFactory;
        /** @var \Magento\Sales\Model\Order $order */
        // $order = $payment->getOrder();
        // $order->getIncrementId();
        /* @var $order \Magento\Sales\Model\Order */

        $order = $orderFactory->create()->loadByIncrementId($orderId);
        //$payment= $order->getPayment();

        // return $payment->getAmount();
        return $order->getGrandTotal();
    }

    protected function getOrder($orderId)
    {
        $orderFactory=$this->orderFactory;
        return $orderFactory->create()->loadByIncrementId($orderId);

    }

    protected function getCustomerInfo($customerId) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
    }

    /**
     * Get payment instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    public function getNumberFormat($price)
    {
        return number_format($price, 2);
    }
    
    public static function renderScript($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
    {
        return Sdk\PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
    }

    /**
     * Set order state and status
     *
     * @param string $paymentAction
     * @param \Magento\Framework\Object $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = $this->getConfigData('order_status');
        $this->_test=$this->getConfigData('test_mode');
        $stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
    }

    public function getPostData($orderId)
    {
        $order = $this->getOrder($orderId);

        $api_key = $this->getConfigData('api_key');

        $customer_id = $order->getCustomerId();

        $customer = $this->getCustomerInfo($customer_id) ;

        $options = new Sdk\PurchaseOptions($customer->getEmail()) ;

        if ($this->_test == "test") {
            $options->setTestEnabled(true);
        }
        $options->setRedirectUrl($this->getOrderPlaceRedirectUrl());
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $resolver = $om->get('Magento\Framework\Locale\Resolver');
        $language = substr($resolver->getLocale(), 0, strpos($resolver->getLocale(), "_")) ;
        $checkout = null ;
        // Create items list
        foreach( $order->getAllItems() as $items )
        {
            if ($checkout == null) {
                $checkout = $options->withCheckout()->addItem(Sdk\CheckoutItem::of($items->getQtyOrdered(), $items->getSku())
                    ->addName($language, $items->getName())
                    ->addPrice($order->getOrderCurrencyCode(), $this->getNumberFormat( $items->getPrice()))
                ) ;
            }
            else
            {
                $checkout->addItem(Sdk\CheckoutItem::of($items->getQtyOrdered(), $items->getSku())
                    ->addName($language, $items->getName())
                    ->addPrice($order->getOrderCurrencyCode(), $this->getNumberFormat( $items->getPrice()))
                ) ;
            }
        }
        if ($order->getShippingAmount() > 0) {
            $checkout->addItem(Sdk\CheckoutItem::of(1, "SHIPPING")
                ->addName($language, "Shipping")
                ->addPrice($order->getOrderCurrencyCode(), $this->getNumberFormat( $order->getShippingAmount()))
            ) ;
        }
        $checkout->addTotal($order->getOrderCurrencyCode(), $order->getGrandTotal());
        $data = array("apiKey"=>$api_key, "options"=>$options) ;

        return $data;
    }

    public function process($responseData)
    {
        $debugData = ['response' => $responseData];
        $this->_debug($debugData);

        // $this->mapGatewayResponse($responseData, $this->getResponse());
        if(count($responseData)>2){
            $order = $this->getOrder(sprintf("%010s",$responseData['InvId']));



            if ($order) {
                echo $this->_processOrder($order,$responseData);
            }
        }else{
            echo "errors";
        }
    }

    protected function _processOrder(\Magento\Sales\Model\Order $order , $response)
    {
        //$response = $this->getResponse();
        $payment = $order->getPayment();
        //$payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
        //TODO: add validation for request data

        try {
            $errors = array();
            //$this->readConfig();
            //$order = Mage::getModel("sales/order")->load($this->getOrderId($answer));
            //$order = Mage::getModel("sales/order")->loadByIncrementId($this->getOrderId($answer));
/*            $hashArray = array(
                $response["OutSum"],
                $response["InvId"],
                $this->getConfigData('pass_word_2')
            );

            $hashCurrent = strtoupper(md5(implode(":", $hashArray)));
            $correctHash = (strcmp($hashCurrent, strtoupper($response['SignatureValue'])) == 0);

            if (!$correctHash) {
                $errors[] = "Incorrect HASH (need:" . $hashCurrent . ", got:"
                    . strtoupper($response['SignatureValue']) . ") - fraud data or wrong secret Key";
                $errors[] = "Maybe success payment";
            }*/

            /**
             * @var $order Mage_Sales_Model_Order
             */
            // if ($this->_transferCurrency != $order->getOrderCurrencyCode()) {
            //     $outSum = round(
            //         $order->getBaseCurrency()->convert($order->getBaseGrandTotal(), $this->_transferCurrency),
            //         2
            //     );
            // } else {
            $outSum = round($order->getGrandTotal(), 2);
            // }

            if ($outSum != $response["OutSum"]) {
                $errors[] = "Incorrect Amount: " . $response["OutSum"] . " (need: " . $outSum . ")";
            }

            // if (count($errors) > 0) {
            //     return $errors;
            // }

            //return (bool)$correctHash;
            //$payment->registerCaptureNotification($payment->getBaseAmountAuthorized());
//            if (!$correctHash) {
//                $payment->setTransactionId($response["InvId"])->setIsTransactionClosed(0);
                $order->setStatus(Order::STATE_PAYMENT_REVIEW);
                $order->save();
                return "Ok" . $response["InvId"];
//            }

            //
        } catch (Exception $e) {
            return array("Internal error:" . $e->getMessage());
        }
    }

}
