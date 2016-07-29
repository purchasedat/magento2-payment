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

    protected $escaper;
    
    protected $widget_params;

    protected $widget_target;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper
    )
    {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
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
                    'payButton' => $this->getPayButton(),
                    'widgetUrl' => $this->getPayButtonWidget(),
                    'params' => $this->getPayButtonParams(),
                    'target' => $this->getPayButtonTarget()
                ],
            ],
        ];
    }
    protected function getMailingAddress()
    {
        $this->method->getMailingAddress();
    }
    protected function getPayButton()
    {
        $button_code = $this->method->getPayButton();
        if (preg_match("/token\":\"(.*?)\"/", $button_code, $matches)) {
            $this->widget_params = $matches[1] ;
        }
        else {
            $this->widget_params = "nincs parameter" ;
        }
        if (preg_match("/target\":\"(.*?)\"/", $button_code, $matches)) {
            $this->widget_target = $matches[1] ;
        }
        else {
            $this->widget_target = "null" ;
        }
        return $button_code;
    }

    protected function getPayButtonWidget() {
        return htmlspecialchars(Sdk\Constants::DEFAULT_WIDGET_URL) ;
    }

    protected function getPayButtonParams() {
        return $this->widget_params ;
    }

    protected function getPayButtonTarget() {
        return $this->widget_target ;
    }
}