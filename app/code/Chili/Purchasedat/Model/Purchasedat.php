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
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Store\Model\StoreManagerInterface;


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
    const PAYMENT_METHOD_PURCHASEDAT_CODE = 'purchasedat';
    protected $_code = self::PAYMENT_METHOD_PURCHASEDAT_CODE;

    /**
     * @var Quote|null
     */
    protected $_quote = null;


    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

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
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart ;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session ;

    /**
     * @var Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $current_customer ;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storemanager ;

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
     * @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface $quoteManagement
     */
    protected $quoteManagement;

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
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []){
        $this->_checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->cart = $cart ;
        $this->session = $session ;
        $this->current_customer = $currentCustomer ;
        $this->storemanager = $storemanager ;
        $this->quoteRepository = $quoteRepository ;
        $this->quoteManagement = $quoteManagement;
        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
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


    public function getPostData($quote, $guest_email = "")
    {
        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
        $quote->collectTotals();
        $quote->save();
        $customer_id = $this->current_customer->getCustomerId();
        $customer = $this->getCustomerInfo($customer_id);
/*        $fp = fopen('email.txt', 'w');
        fwrite($fp, "aaa: " . print_r(get_object_vars($quote->getBillingAddress()), true));
        fclose($fp);*/
        if ($quote->getGrandTotal() && ($customer->getEmail() || $guest_email != "")) {
            $quote_data = $quote->getData();
            $grand_total = $quote_data['grand_total'];
            $subtotal = $quote_data['subtotal_with_discount'];
            $api_key = $this->getConfigData('api_key');
            if ($customer->getEmail()) {
                $customer_email = $customer->getEmail() ;
            }
            else
            {
                $customer_email = $guest_email ;
            }
            $options = new Sdk\PurchaseOptions($customer_email);
            if ($this->_test == "test") {
                $options->setTestEnabled(true);
            }
            $baseUrl = $this->storemanager->getStore()->getBaseUrl();
            $options->setRedirectUrl($baseUrl . 'purchasedat/payment/finish');
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resolver = $om->get('Magento\Framework\Locale\Resolver');
            $language = substr($resolver->getLocale(), 0, strpos($resolver->getLocale(), "_"));
            $currency_code = $quote_data['global_currency_code'];
            $checkout = null;

            $shipping_rate = $grand_total - $subtotal;
            // Create items list
            foreach ($this->session->getQuote()->getAllItems() as $items) {
                if ($checkout == null) {
                    $checkout = $options->withCheckout()->addItem(Sdk\CheckoutItem::of((int)$items->getQty(), $items->getSku())
                        ->addName($language, $items->getName())
                        ->addPrice($currency_code, $this->getNumberFormat($items->getPrice()))
                    );
                } else {
                    $checkout->addItem(Sdk\CheckoutItem::of((int)$items->getQty(), $items->getSku())
                        ->addName($language, $items->getName())
                        ->addPrice($currency_code, $this->getNumberFormat($items->getPrice()))
                    );
                }
            }
            if ($shipping_rate > 0) {
                $checkout->addItem(Sdk\CheckoutItem::of(1, "SHIPPING")
                    ->addName($language, "Shipping")
                    ->addPrice($currency_code, $this->getNumberFormat($shipping_rate))
                );
            }
            $checkout->addTotal($currency_code, $grand_total);
            $data = array("apiKey" => $api_key, "options" => $options);
        }
        else
        {
            $data = false ;
        }
        return $data;
    }

    public function getPayButton()
    {
        $paybutton_code = "" ;
        $quote= $this->cart->getQuote();
        $data = $this->getPostData($quote) ;
        if ($data) {
            $paybutton_code = self::renderScript($data["apiKey"], $data["options"]);
        }
        return $paybutton_code;
    }
    
}
