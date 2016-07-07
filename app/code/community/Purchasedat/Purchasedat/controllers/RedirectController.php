<?php
/**
 * RedirectController.php
 * 
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */

// Include the purchasedat common file
define( 'PB_DEBUG', ( Mage::getStoreConfig( 'payment/purchasedat/debugging' ) ? true : false ) );
include_once( dirname( __FILE__ ) .'/../purchasedat_common.inc' );
 
/**
 * Purchasedat_RedirectController
 */
class Purchasedat_Purchasedat_RedirectController extends Mage_Core_Controller_Front_Action
{
    protected $_order;
	protected $_WHAT_STATUS = false;

    public function getOrder()
    {
       
        return( $this->_order );
    }

    protected function _expireAjax()
    {
     
        if( !Mage::getSingleton( 'checkout/session' )->getQuote()->hasItems() )
        {
            $this->getResponse()->setHeader( 'HTTP/1.1', '403 Session Expired' );
            exit;
        }
    }
    
    protected function _getCheckout()
    {
      
        return Mage::getSingleton( 'checkout/session' );
    }
  
	public function getQuote()
    {
      
        return $this->getCheckout()->getQuote();
    }
 
    public function getStandard()
    {
        
        return Mage::getSingleton( 'purchasedat/standard' );
    }
    
	public function getConfig()
    {
       
        return $this->getStandard()->getConfig();
    }

    protected function _getPendingPaymentStatus()
    {
         
        return Mage::helper( 'purchasedat' )->getPendingPaymentStatus();
    }
  
    public function redirectAction()
    {

        pblog( 'Redirecting to purchasedat' );
        
		try
        {
            $session = Mage::getSingleton( 'checkout/session' );

            $order = Mage::getModel( 'sales/order' );
            $order->loadByIncrementId( $session->getLastRealOrderId() );
        
            if( !$order->getId() )
                Mage::throwException( 'No order for processing found' );
        
            if( $order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT )
            {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    $this->_getPendingPaymentStatus(),
                    Mage::helper( 'purchasedat' )->__( 'Customer was redirected to Purchased.at.' )
                )->save();
            }

            if( $session->getQuoteId() && $session->getLastSuccessQuoteId() )
            {
                $session->setpurchasedatQuoteId( $session->getQuoteId() );
                $session->setpurchasedatSuccessQuoteId( $session->getLastSuccessQuoteId() );
                $session->setpurchasedatRealOrderId( $session->getLastRealOrderId() );
                $session->getQuote()->setIsActive( false )->save();
                $session->clear();
            }

			$r = $this->getResponse()->setBody( $this->getLayout()->createBlock( 'purchasedat/request' )->toHtml() );  

        
	        $session->unsQuoteId();
            
            return;
        }
        catch( Mage_Core_Exception $e )
        {
            $this->_getCheckout()->addError( $e->getMessage() );
        }
        catch( Exception $e )
        {
            Mage::logException($e);
        }       
        
        $this->_redirect( 'checkout/cart' );
    }
   
    public function cancelAction()
    {
        
		// Get the user session
        $session = Mage::getSingleton( 'checkout/session' );
        $session->setQuoteId( $session->getpurchasedatQuoteId( true ) );
		$session = $this->_getCheckout();


        $arrParams = $this->getRequest()->getParams();
        Mage::getModel('purchasedat/standard')->getResponseOperation($arrParams);
       
        
        if( $quoteId = $session->getpurchasedatQuoteId() )
        {
            $quote = Mage::getModel( 'sales/quote' )->load( $quoteId );
            
            if( $quote->getId() )
            {
                $quote->setIsActive( true )->save();
                $session->setQuoteId( $quoteId );
            }
        }
		
        // Cancel order
		$order = Mage::getModel( 'sales/order' )->loadByIncrementId( $session->getLastRealOrderId() );
		if( $order->getId() )
            $order->cancel()->save();

        $this->_redirect('checkout/cart');
    }

     public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }


     public function failureAction(){
       $arrParams = $this->getRequest()->getPost();
       Mage::getModel('purchasedat/standard')->getResponseOperation($arrParams);
       $this->getCheckout()->clear();
       $this->_redirect('checkout/onepage/failure');
    }


    public function successAction()
    {     
      
		try
        {
			$session = Mage::getSingleton( 'checkout/session' );
			$session->unspurchasedatRealOrderId();
			$session->setQuoteId( $session->getpurchasedatQuoteId( true ) );
			$session->setLastSuccessQuoteId( $session->getpurchasedatSuccessQuoteId( true ) );
            $response = $this->getRequest()->getPost();
            Mage::getModel('purchasedat/standard')->getResponseOperation($response);

			$this->_redirect( 'checkout/onepage/success', array( '_secure' => true ) );
			
            return;
		}
        catch( Mage_Core_Exception $e )
        {
			$this->_getCheckout()->addError( $e->getMessage() );
		}
        catch( Exception $e )
        {
			Mage::logException( $e );
		}
		
        $this->_redirect( 'checkout/cart' );
    }
   
}