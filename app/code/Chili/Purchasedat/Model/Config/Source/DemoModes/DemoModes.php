<?php
/**
 *
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */
namespace Chili\Purchasedat\Model\Config\Source\DemoModes;

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