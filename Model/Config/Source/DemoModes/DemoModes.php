<?php

namespace PurchasedAt\Magento2Payment\Model\Config\Source\DemoModes;

class DemoModes
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'test', 'label' => 'Test'),
            array('value' => 'live', 'label' => 'Live'),
        );
    }
}