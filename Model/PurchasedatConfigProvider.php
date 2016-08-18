<?php

namespace PurchasedAt\Magento2Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class PurchasedatConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = \PurchasedAt\Magento2Payment\Model\Purchasedat::PAYMENT_METHOD_PURCHASEDAT_CODE;

    protected $method;

    protected $widget_params;

    protected $widget_target;

    protected $button_code;

    protected $urlBuider;

    protected $storemanager;

    protected $assetRepository;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
    	\Magento\Framework\View\Asset\Repository $assetRepository
    )
    {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->urlBuilder = $urlBuilder;
        $this->button_code = $this->method->getPayButton();
        $this->storemanager = $storemanager;
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'purchasedat' => [
                    'instructions' => $this->method->getInstructions(),
                    'params' => $this->getPayButtonParams(),
                    'target' => $this->getPayButtonTarget(),
                    'ajax_url' => $this->getAjaxCallUrl(),
                    'logo_url' => $this->getLogoURL()
                ],
            ],
        ];
    }
    protected function getInstructions()
    {
        return $this->method->getConfig("instructions");
    }

    protected function getAjaxCallUrl() {
        return $this->urlBuilder->getUrl('purchasedat/ajaxdata/index', $paramsHere = array());
    }

    protected function getLogoURL() {
        return $this->assetRepository->getUrl("PurchasedAt_Magento2Payment::images/pat-logo.png");
    }

    protected function getPayButtonParams() {
        if (preg_match("/token\":\"(.*?)\"/", $this->button_code, $matches)) {
            $this->widget_params = $matches[1] ;
        }
        else {
            $this->widget_params = "null";
        }
        return $this->widget_params ;
    }

    protected function getPayButtonTarget() {
        if (preg_match("/target\":\"(.*?)\"/", $this->button_code, $matches)) {
            $this->widget_target = $matches[1] ;
        }
        else {
            $this->widget_target = "null" ;
        }
        return $this->widget_target ;
    }
}