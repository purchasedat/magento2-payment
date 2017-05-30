<?php

namespace PurchasedAt\Magento2Payment\Block\Payment;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use PurchasedAt\API;
use PurchasedAt\APIClient;
use PurchasedAt\Magento2Payment\Helper;

class Finish extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart ;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface,
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\Quote\Model\QuoteFactory,
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $_quoteManagement;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /** @var \PurchasedAt\Magento2Payment\Model\PurchasedatModel */
    protected $_patModel;

    /**
     * Helper
     *
     * @var \PurchasedAt\Magento2Payment\Helper\Data
     */
    protected $_helper;

    /**
     * Finish constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param Order\Config $orderConfig
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \PurchasedAt\Magento2Payment\Model\PurchasedatModel $patModel
     * @param \PurchasedAt\Magento2Payment\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\App\Http\Context $httpContext,
        \PurchasedAt\Magento2Payment\Model\PurchasedatModel $patModel,
        \PurchasedAt\Magento2Payment\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart ;
        $this->_orderFactory = $orderFactory;
        $this->_orderConfig = $orderConfig;
        $this->orderSender = $orderSender;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteFactory = $quoteFactory;
        $this->_quoteManagement = $quoteManagement;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->_patModel = $patModel;
        $this->_helper = $helper;
    }

    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * Set custom session data
     * @param $key
     * @param $value
     * @return mixed
     */
    public function getSessionData($key, $clear = false)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $om->create('\Magento\Checkout\Model\Session');
        return $checkoutSession->getData($key, $clear);
    }

    /**
     * Send an e-mail to the administrator about the failed transaction
     * @param \Magento\Quote\Model\Quote $checkout
     * @param object $transaction_details
     */
    public function sendTransactionEmail($checkout, $transaction_details) {
        $transactionVariables = array();
        $transactionVariables['transactionID'] = $transaction_details->getID();
        $transactionVariables['transactionState'] = $transaction_details->getState();
        $test_mode = "Live" ;
        if ($transaction_details->isTest()) {
            $test_mode = "Test" ;
        }
        $transactionVariables['transactionTest'] = $test_mode;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get('PurchasedAt\Magento2Payment\Helper\Email')->mailSendMethod(
            $transactionVariables,
            $checkout
        );
    }

    /**
     * Prepares block data and process the response.
     * If the purchase process was successful, create the order, otherwise show the error message
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $api_key = $this->_helper->getConfig('payment/purchasedat/api_key');
        $apiClient = new APIClient($api_key);

// verify the redirect comes from purchased.at
// and fetch the corresponding transaction
        $result = $apiClient->fetchTransactionForRedirect();
        $magento_transaction_id = $result->result->getExternalId() ;

        $order_id = substr($magento_transaction_id, 0, strpos($magento_transaction_id, "-")) ;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order_id);

        $error_message = "" ;
        $result_message = "" ;
        if(!$result->success) {
            $error_message = "Request failed: " . $result->errorCode;
        }
        else
        {
            $transaction = $result->result;

// handle transactions that cannot become
// successful anymore
            if( $transaction->getState()!='successful' &&
                $transaction->getState()!='pending' ) {
                $error_message = sprintf('Transaction not successful: id=%s, ' .
                    'state=%s<br>',
                    $transaction->getId(),
                    $transaction->getState()
                );
                if ($transaction->isTest()) {
                    $error_message .= "<br><strong>This was a TEST transaction</strong><br />" ;
                }

                $this->sendTransactionEmail($order, $transaction);
            }
            else
            {
                //Everything went okay
                $customer = $transaction->getCustomer();
                $price    = $transaction->getPrice();


// pending transactions are awaiting payment
// and can become successful later
                if( $transaction->getState() == 'pending' ) {
                    $result_message = 'We received your order, but we are still ' .
                        'waiting for confirmation of the payment.<br>';
                    $order->setStatus(Order::STATE_PENDING_PAYMENT);
                }
                else if ($transaction->getState() == 'successful') {
                    $order->setBaseTotalPaid($price->getGross());
                    $order->setTotalPaid($this->_helper->convertPrice($price->getGross()));
                }

                $result_message .= sprintf('Transaction details:<br />Id:%s<br />Customer:%s (country:%s)<br /> ' .
                    'Total price:%s %s',
                    $transaction->getId(),
                    $customer->getEmail(),
                    $customer->getCountry(),
                    $price->getGross(),
                    $price->getCurrency()) ;

                if ($transaction->isTest()) {
                    $result_message .= "<br><strong>This was a TEST transaction</strong><br />" ;
                }
                $this->_patModel->createMagentoTransaction($order, $result->result) ;
                $this->orderSender->send($order);
            }
        }
        $this->addData(
            [
                'error_message' => $error_message,
                'result_message' => $result_message,
                'order_id'  => $order_id

            ]
        );
    }

}
