<?php

namespace PurchasedAt\Magento2Payment\Controller\Payment;

class Finish extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    /**
     * Finish constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Get custom session data
     * @param $key
     * @param bool $remove
     * @return mixed
     */
    public function getSessionData($key, $remove = false)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $catalogSession = $om->create('Magento\Catalog\Model\Session');
        return $catalogSession->getData($key, $remove);
    }

    /**
     * Load the page defined in view/frontend/layout/purchasedat_payment_finish.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
