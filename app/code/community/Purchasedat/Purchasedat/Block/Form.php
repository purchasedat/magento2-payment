<?php
/**
 * Form.php
 *
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */

/**
 * Purchasedat_Block_Form 
 */
class Purchasedat_Purchasedat_Block_Form extends Mage_Payment_Block_Form
{
    // {{{ _construct()
    /**
     * _construct() 
     */    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate( 'purchasedat/form.phtml' );
    }
    // }}}
}