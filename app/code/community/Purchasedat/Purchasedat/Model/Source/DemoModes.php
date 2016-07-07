<?php
/**
 *
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */

class Purchasedat_Purchasedat_Model_Source_DemoModes
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'test', 'label' => 'Test'),
            array('value' => 'live', 'label' => 'Live'),
        );
    }
}