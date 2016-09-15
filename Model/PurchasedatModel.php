<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PurchasedAt\Magento2Payment\Model;

use PurchasedAt\API;
use PurchasedAt;
use PurchasedAt\PurchaseOptions;
use PurchasedAt\PurchaseScript;
use PurchasedAt\Purchase\CheckoutItem as PurchaseCheckoutItem;
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
 * Purchased.at payment method model
 */
class PurchasedatModel extends \Magento\Payment\Model\Method\AbstractMethod
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
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart ;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session ;

    /**
     * @var Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $_current_customer ;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storemanager ;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    protected $_quoteRepository;

    /**
     * Test mode or live
     *
     * @var string
     */
    protected $_test = 'test';

    /**
     * PurchasedatModel constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param Cart $cart
     * @param \Magento\Checkout\Model\Session $session
     * @param StoreManagerInterface $storemanager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param CurrentCustomer $currentCustomer
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
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
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []){
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart ;
        $this->_session = $session ;
        $this->_current_customer = $currentCustomer ;
        $this->_storemanager = $storemanager ;
        $this->_priceCurrency = $priceCurrency;
        $this->_quoteRepository = $quoteRepository ;
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


    /**
     * Load and return customer details by customer ID
     * @param $customerId
     * @return mixed
     */
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

    /**
     * Return the number formatted price
     * @param $price
     * @return string
     */
    public function getNumberFormat($price)
    {
        return number_format($price, 2);
    }

    /**
     * Static function call to purchased.at's render script render method.
     * On the most of webshop engines we can inject this code onto the checkout page and it will display the pay button, but in magento 2 it is not working.
     * So, we will process this code and its fragments
     * @param $apiKey
     * @param $purchaseOptions
     * @param null $target
     * @param null $jwtOptions
     * @return string The rendered html and javascript.
     */
    public static function renderScript($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
    {
        return PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
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


    /**
     * Get and return the quote details in the expected structure by purchased.at and api key in an array with two fields
     * @param $quote
     * @param string $guest_email: If the buyer is not logged in, we use that e-mail address, what he set on billing address form
     * @return array|bool
     */
    public function getPostData($quote, $guest_email = "")
    {
        $quote->reserveOrderId();
        $this->_quoteRepository->save($quote);
        $quote->collectTotals();
        $quote->save();
        $customer_id = $this->_current_customer->getCustomerId();
        $customer = $this->getCustomerInfo($customer_id);
        if ($quote->getGrandTotal() && ($customer->getEmail() || $guest_email != "")) {
            $quote_data = $quote->getData();
            $grand_total = $quote_data['grand_total'];
            $api_key = $this->getConfigData('api_key');
            if ($customer->getEmail()) {
                $customer_email = $customer->getEmail() ;
            }
            else
            {
                $customer_email = $guest_email ;
            }
            $options = new PurchaseOptions($customer_email);
            if ($this->_test == "test") {
                $options->setTestEnabled(true);
            }
            $baseUrl = $this->_storemanager->getStore()->getBaseUrl();
            $options->setRedirectUrl($baseUrl . 'purchasedat/payment/finish');
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $resolver = $om->get('Magento\Framework\Locale\Resolver');
            $language = substr($resolver->getLocale(), 0, strpos($resolver->getLocale(), "_"));
            $currency_code = $this->_storemanager->getStore()->getCurrentCurrency()->getCode() ;
            $checkout = null;

            $shipping_rate = $quote_data['shipping_amount'] ;
            // Create items list
            foreach ($this->_session->getQuote()->getAllItems() as $items) {
                if ($checkout == null) {
                    $checkout = $options->withCheckout()->addItem(PurchaseCheckoutItem::of((int)$items->getQty(), $items->getSku())
                        ->addName($language, $items->getName())
                        ->addPrice($currency_code, $this->getNumberFormat($this->_priceCurrency->convert($items->getPrice())))
                    );
                } else {
                    $checkout->addItem(PurchaseCheckoutItem::of((int)$items->getQty(), $items->getSku())
                        ->addName($language, $items->getName())
                        ->addPrice($currency_code, $this->getNumberFormat($this->_priceCurrency->convert($items->getPrice())))
                    );
                }
            }
            if ($shipping_rate > 0) {
                $checkout->addItem(PurchaseCheckoutItem::of(1, "SHIPPING")
                    ->addName($language, "Shipping")
                    ->addPrice($currency_code, $this->getNumberFormat($shipping_rate))
                );
            }
            $checkout->addTotal($currency_code, $this->getNumberFormat($grand_total));
            $data = array("apiKey" => $api_key, "options" => $options);
        }
        else
        {
            $data = false ;
        }
        return $data;
    }

    /**
     * Collect the quote details, get the purchased.at pay button's html / js coda and return it.
     * @return string
     */
    public function getPayButton()
    {
        $paybutton_code = "" ;
        $quote= $this->_cart->getQuote();
        $data = $this->getPostData($quote) ;
        if ($data) {
            $paybutton_code = self::renderScript($data["apiKey"], $data["options"]);
        }
        return $paybutton_code;
    }

}