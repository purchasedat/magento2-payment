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
        $parameters = $standard->getStandardCheckoutFormFields() ;
        pblog( print_r($parameters, true) );
        
        $html = "<html><body><button id=\"proceed_button\" data-purchasedat-widget-open>Proceed...</button>" ;
        $html .= \PurchasedAt\renderScript($parameters["apiKey"], $parameters["options"]) ;
        $html .= '<script type="text/javascript">' ;
        $html .= 'var event = new Event(\'click\');' ;
        $html .= '$("#proceed_button")[0].dispatchEvent(event);document.getElementById( "proceed_button" ).click();' ;        
        $html .= '</script>';
        $html .= '</body></html>';
        
       return $html;
    }
}