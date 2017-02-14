<?php
namespace PurchasedAt\Magento2Payment\Helper;

use Magento\Store\Model\Store;

/**
 * Custom Module Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'payment/purchasedat/payment_failed_template';
//    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'checkout/payment_failed/template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_localeDate = $localeDate;
        $this->_transportBuilder = $transportBuilder;
    }

    protected function _getEmails($configPath, $storeId)
    {
        $data = $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }


    /**
     * [generateTemplate description]  with template file and templates variables values
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_BACKEND, /* here you can defile area and
                                                                                 store of template for which you prepare it */
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'],$receiverInfo['name']);
        return $this;
    }

    /**
     * [sendInvoicedOrderEmail description]
     * @param  Mixed $transactionVariables
     * @param  \Magento\Quote\Model\Quote $checkout
     * @return void
     */
    public function mailSendMethod($transactionVariables, $checkout)
    {
        $this->inlineTranslation->suspend();

        $template = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE_FIELD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $checkout->getStoreId()
        );

        $copyTo = $this->_getEmails('checkout/payment_failed/copy_to', $checkout->getStoreId());
        $copyMethod = $this->scopeConfig->getValue(
            'checkout/payment_failed/copy_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $checkout->getStoreId()
        );
        $bcc = [];
        if ($copyTo && $copyMethod == 'bcc') {
            $bcc = $copyTo;
        }

        $_receiver = $this->scopeConfig->getValue(
            'checkout/payment_failed/receiver',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $checkout->getStoreId()
        );
        $sendTo = [
            [
                'email' => $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $checkout->getStoreId()
                ),
                'name' => $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $checkout->getStoreId()
                ),
            ],
        ];


        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = ['email' => $email, 'name' => null];
            }
        }
        $shippingAddress = "" ;
        $shippingAddressObj = $checkout->getShippingAddress();
        if ($shippingAddressObj != null) {
            $shippingAddress = $shippingAddressObj->getName() . ", " ;
            $streetLines = $shippingAddressObj->getStreet();
            foreach ($streetLines as $lineNumber => $lineValue) {
                $shippingAddress .= $lineValue . " " ;
            }
            $shippingAddress .= $shippingAddressObj->getCity() . ", " . $shippingAddressObj->getPostcode() . ", " . $shippingAddressObj->getCountryId() ;
        }

        $billingAddress = "" ;
        $billingAddressObj = $checkout->getBillingAddress();
        if ($billingAddressObj != null) {
            if ($billingAddressObj->getCompany() != "") {
                $billingAddress = $billingAddressObj->getCompany() . ", ";
            }
            else {
                $billingAddress = $billingAddressObj->getName() . ", ";
            }
            $streetLines = $billingAddressObj->getStreet();
            foreach ($streetLines as $lineNumber => $lineValue) {
                $billingAddress .= $lineValue . " " ;
            }
            $billingAddress .= $billingAddressObj->getCity() . ", " . $billingAddressObj->getPostcode() . ", " . $billingAddressObj->getCountryId() ;
        }

        $paymentMethod = '';
        if ($paymentInfo = $checkout->getPayment()) {
            $paymentMethod = $paymentInfo->getMethod();
        }

        $items = '';
        foreach ($checkout->getAllVisibleItems() as $_item) {
            /* @var $_item \Magento\Quote\Model\Quote\Item */
            $items .=
                $_item->getProduct()->getName() . '  x ' . $_item->getQty() . '  ' . $checkout->getStoreCurrencyCode()
                . ' ' . $_item->getProduct()->getFinalPrice(
                    $_item->getQty()
                ) . "\n";
        }
        $total = $checkout->getStoreCurrencyCode() . ' ' . $checkout->getGrandTotal();

        foreach ($sendTo as $recipient) {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID
                ]
            )->setTemplateVars(
                [
                    'orderId' => $checkout->getRealOrderId(),
                    'patTransId' => $transactionVariables['transactionID'],
                    'patTransState' => $transactionVariables['transactionState'],
                    'patTestMode' => $transactionVariables['transactionTest'],
                    'dateAndTime' => $this->_localeDate->formatDateTime(
                        new \DateTime(),
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM
                    ),
                    'customer' => $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
                    'customerEmail' => $checkout->getCustomerEmail(),
                    'billingAddress' => $billingAddress,
                    'shippingAddress' => $shippingAddress,
                    'shippingMethod' => $checkout->getShippingDescription(),
                    'paymentMethod' => $this->scopeConfig->getValue(
                        'payment/' . $paymentMethod . '/title',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                    'items' => nl2br($items),
                    'total' => $total,
                ]
            )->setFrom(
                $this->scopeConfig->getValue(
                    'checkout/payment_failed/identity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $checkout->getStoreId()
                )
            )->addTo(
                $recipient['email'],
                $recipient['name']
            )->addBcc(
                $bcc
            )->getTransport();

            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();

        return $this;
    }

}