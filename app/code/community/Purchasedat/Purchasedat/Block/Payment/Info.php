<?php
/**
 * Info.php
 * 
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */
 
/**
 * Purchasedat_Block_Payment_Info 
 */
class Purchasedat_Purchasedat_Block_Payment_Info extends Mage_Payment_Block_Info
{
    // {{{ _prepareSpecificInformation()
    /**
     * _prepareSpecificInformation 
     */
    protected function _prepareSpecificInformation( $transport = null )
    {
        $transport = parent::_prepareSpecificInformation( $transport );
        $payment = $this->getInfo();
        $pbInfo = Mage::getModel( 'purchasedat/info' );
        
        if( !$this->getIsSecureMode() )
            $info = $pbInfo->getPaymentInfo( $payment, true );
        else
            $info = $pbInfo->getPublicPaymentInfo( $payment, true );

        return( $transport->addData( $info ) );
    }
    // }}}
}