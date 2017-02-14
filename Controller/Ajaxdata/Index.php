<?php
namespace PurchasedAt\Magento2Payment\Controller\Ajaxdata;

use Magento\Sales\Model\Order;

/**
 * Return data in json format
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $_jsonResultFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart ;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;

    /** @var \PurchasedAt\Magento2Payment\Model\PurchasedatModel */
    protected $_patModel;

    /**
     * @var string $button_code
     */
    protected $button_code;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \PurchasedAt\Magento2Payment\Model\PurchasedatModel $patModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \PurchasedAt\Magento2Payment\Model\PurchasedatModel $patModel
    ) {
        parent::__construct($context);
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart ;
        $this->_quoteRepository = $quoteRepository ;
        $this->_patModel = $patModel;
    }

    /**
     * Set custom session data
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setSessionData($key, $value)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $catalogSession = $om->create('\Magento\Catalog\Model\Session');
        return $catalogSession->setData($key, $value);
    }

    /**
     * Show / return the token and target info from the script that is rendered by purchased.at SDK
     * To set up our Magento 2 javascript function what run the purchased.at widget javascript, we need the token and the target info
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->_jsonResultFactory->create();
        $quote= $this->_cart->getQuote();
        $request = $this->getRequest();
        $guest_email = $request->getParam("email");
        $data = $this->_patModel->getPostData($quote, $guest_email);
        if ($data) {
            $this->button_code = $this->_patModel->renderScript($data["apiKey"], $data["options"]);
            $token = $this->getPayButtonParams();
            $target = $this->getPayButtonTarget();
            $result->setData(['token' => $token, 'target' => $target]);
            $this->setSessionData("button_token", $token) ;
            $this->setSessionData("button_target", $target) ;
        } else {
            $result->setData(['Error' => "Error happened!"]);
        }
        if ($quote != null) {
            $this->createOrder($quote, $guest_email);
        }
        return $result;
    }

    /**
     * Save quote to order
     * @param $quote
     * @param $guest_email
     * @return int
     */
    protected function createOrder($quote, $guest_email = "") {
        $quote->setPaymentMethod('purchasedat'); //payment method
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $om->get('\Magento\Customer\Model\Session');
        if(!$customerSession->isLoggedIn()) {
            $quote->setCustomerId(null)
                ->setCustomerEmail($guest_email)
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }
        $quote->save(); //Now Save quote and your quote is ready

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => 'purchasedat']);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        $quoteManagement = $om->create('\Magento\Quote\Model\QuoteManagement');
        $order = $quoteManagement->submit($quote);
        $order_id = -1;
        if ($order != null) {
            $order->setEmailSent(0);
            if ($order->getEntityId()) {
                $order_id = $order->getRealOrderId();
            } else {
                $order_id = -1;
            }
            $order->setStatus(Order::STATE_PENDING_PAYMENT) ;
            $order->save();
        }
        return $order_id ;
    }

    /**
     * From the purchased.at script generated by purchased.at SDK we get and return the token
     * @return string
     */
    protected function getPayButtonParams() {
        if (preg_match("/token\":\"(.*?)\"/", $this->button_code, $matches)) {
            $token = $matches[1] ;
        }
        else {
            $token = "null";
        }
        return $token ;
    }

    /**
     * From the purchased.at script generated by purchased.at SDK we get and return the target info
     * @return string
     */
    protected function getPayButtonTarget() {
        if (preg_match("/target\":\"(.*?)\"/", $this->button_code, $matches)) {
            $target = $matches[1] ;
        }
        else {
            $target = "null" ;
        }
        return $target ;
    }


}