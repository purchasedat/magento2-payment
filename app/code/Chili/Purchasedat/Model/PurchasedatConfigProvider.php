<?php
/**
 * Created by PhpStorm.
 * User: Monsz
 * Date: 2016.07.25.
 * Time: 0:26
 */
namespace Chili\Purchasedat\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class PurchasedatConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = \Chili\Purchasedat\Model\Purchasedat::PAYMENT_METHOD_PURCHASEDAT_CODE;

    protected $method;
    
    protected $widget_params;

    protected $widget_target;

    protected $button_code;

    protected $urlBuider;
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->urlBuilder = $urlBuilder;
        $this->button_code = $this->method->getPayButton();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'purchasedat' => [
                    'mailingAddress' => $this-> getMailingAddress(),
                    'params' => $this->getPayButtonParams(),
                    'target' => $this->getPayButtonTarget(),
                    'ajax_url' => $this->getAjaxCallUrl()
                ],
            ],
        ];
    }
    protected function getMailingAddress()
    {
        $this->method->getMailingAddress();
    }

    protected function getAjaxCallUrl() {
        return $this->urlBuilder->getUrl('purchasedat/ajaxdata/index', $paramsHere = array());
    }

    protected function getPayButtonParams() {
        if (preg_match("/token\":\"(.*?)\"/", $this->button_code, $matches)) {
            $this->widget_params = $matches[1] ;
        }
        else {
            $this->widget_params = "nincs parameter";
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