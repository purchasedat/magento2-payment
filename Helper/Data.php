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

    /**
     * @param float $price
     * @param bool $format
     * @return float
     */
/*
//    Ez miért lett ilyen?
    public function convertPrice($price, $format = true)
    {
    	return $format
    	? $this->priceCurrency->convertAndFormat($price)
    	: $this->priceCurrency->convert($price);
    }
*/

    /**
     * Convert a base price to the current currency, or to $currency, and return it
     * @param float $amount
     * @param object $store = null
     * @param object $currency = null
     */
    public function convertPrice($amount, $store = null, $currency = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrencyObject = $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        if ($store == null) {
            $store = $storeManager->getStore()->getStoreId();
        }
        $rate = $priceCurrencyObject->convert($amount, $store, $currency);
        return $rate ;
    }
}