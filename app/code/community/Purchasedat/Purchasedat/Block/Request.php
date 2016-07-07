<?php
/**
 * Request.php
 *
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */


class Purchasedat_Purchasedat_Block_Request extends Mage_Core_Block_Abstract
{
    
    protected function _toHtml()
    {
        $standard = Mage::getModel( 'purchasedat/standard' );
        $form = new Varien_Data_Form();
        $form->setAction( $standard->getPurchasedatUrl() )
            ->setId( 'purchasedat_checkout' )
            ->setName( 'purchasedat_checkout' )
            ->setMethod( 'POST' )
            ->setUseContainer( true );
        
        foreach( $standard->getStandardCheckoutFormFields() as $field=>$value )
            $form->addField( $field, 'hidden', array( 'name' => $field, 'value' => $value, 'size' => 200 ) );
        
        $html = '<html><body>';
        $html.= $this->__( 'You will be redirected to Purchasedat Payment Gateway in a few seconds.' );
        $html.= $form->toHtml();
		#echo $html;exit;
        $html.= '<script type="text/javascript">document.getElementById( "purchasedat_checkout" ).submit();</script>';
        $html.= '</body></html>';
       return $html;
    }


}