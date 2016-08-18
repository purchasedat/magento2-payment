<?php
namespace PurchasedAt\Magento2Payment\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Return a configuration value from magento 2 configuration system settings by configuration name
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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

}