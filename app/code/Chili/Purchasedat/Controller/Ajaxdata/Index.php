<?php
namespace Chili\Purchasedat\Controller\Ajaxdata;

use Chili\Purchasedat\Model\Sdk\API;
use Chili\Purchasedat\Model\Sdk;

/**
 * Demo of authorization error for custom REST API
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

    /**
     * @var Magento\Customer\Helper\Session\CurrentCustomer
     */
//    protected $_current_customer ;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
//    protected $_storemanager ;

    /**
     * @var Chili\Purchasedat\Helper\Data
     */
//    protected $_helper ;

    /** @var \Chili\Purchasedat\Model\Purchasedat */
    protected $_patModel;

    protected $button_code;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Chili\Purchasedat\Model\Purchasedat $patModel
    ) {
        parent::__construct($context);
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart ;
        $this->_quoteRepository = $quoteRepository ;
        $this->_patModel = $patModel;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->_jsonResultFactory->create();
        /** You may introduce your own constants for this custom REST API */
        $quote= $this->_cart->getQuote();
        $request = $this->getRequest();
        $guest_email = $request->getParam("email");
        $data = $this->_patModel->getPostData($quote, $guest_email) ;
        if ($data) {
            $this->button_code = $this->_patModel->renderScript($data["apiKey"], $data["options"]) ;
            $token = $this->getPayButtonParams() ;
            $target = $this->getPayButtonTarget() ;
            $result->setData(['token' => $token, 'target' => $target]);
        }
        else
        {
            $result->setData(['Error' => "Error happened!"]) ;
        }
        return $result;
    }

    protected function getPayButtonParams() {
        if (preg_match("/token\":\"(.*?)\"/", $this->button_code, $matches)) {
            $token = $matches[1] ;
        }
        else {
            $token = "nincs parameter";
        }
        return $token ;
    }

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