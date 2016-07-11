<?php
/**
 * Standard.php
 * 
 * @author     Árpád Tóth 
 * @category   Purchasedat
 * @package    Purchasedat_Purchasedat
 */

require_once(Mage::getBaseDir('lib') . DS . 'purchasedat' . DS . 'purchasedat_sdk.php');

use \PurchasedAt\Purchase\CheckoutItem;
use \PurchasedAt\PurchaseOptions;

/**
 * Purchasedat_Purchasedat_Model_Standard
 */
class Purchasedat_Purchasedat_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'purchasedat';
    protected $_formBlockType = 'purchasedat/form';
    protected $_infoBlockType = 'purchasedat/payment_info';
    protected $_order;
    
    protected $_isGateway              = true;
    protected $_canAuthorize           = true;
    protected $_canCapture             = true;
    protected $_canCapturePartial      = false;
    protected $_canRefund              = false;
    protected $_canVoid                = true;
    protected $_canUseInternal         = true;
    protected $_canUseCheckout         = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc              = false;

  
    public function getCheckout()
    {
        
        return Mage::getSingleton( 'checkout/session' );
    }

    public function getQuote()
    {
      
        return $this->getCheckout()->getQuote();
    }
  
    public function getConfig()
    {
       
        return Mage::getSingleton( 'purchasedat/config' );
    }
   
    public function getOrderPlaceRedirectUrl()
    {
       
        return Mage::getUrl( 'purchasedat/redirect/redirect', array( '_secure' => true ) );
    }
 
    public function getSuccessUrl()
    {
      
        return Mage::getUrl( 'purchasedat/redirect/success', array( '_secure' => true ) );
    }
 
    public function getCancelUrl()
    {
        return Mage::getUrl( 'purchasedat/redirect/cancel', array( '_secure' => true ) );
    }
  
    public function getfailureUrl()
    { 
      
        return Mage::getUrl( 'purchasedat/redirect/failure', array( '_secure' => true ) );
    }
  
    public function getRealOrderId()
    {
      
        return Mage::getSingleton( 'checkout/session' )->getLastRealOrderId();
    }
  
    public function getNumberFormat( $number )
    {

        return number_format( $number, 2, '.', '' );
    }

    public function getTotalAmount( $order )
    {
      
        if( $this->getConfigData( 'use_store_currency' ) )
            $price = $this->getNumberFormat( $order->getGrandTotal() );
        else
            $price = $this->getNumberFormat( $order->getBaseGrandTotal() );

        return $price;
    }
 
    public function getStoreName()
    {
       
        $store_info = Mage::app()->getStore();
        return $store_info->getName();
    }
    
    public function getStandardCheckoutFormFields()
    {
        // Variable initialization
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel( 'sales/order' )->loadByIncrementId( $orderIncrementId );
        $description = '';

        $transaction_mode = $this->getConfigData('trans_mode');
        $api_key = $this->getConfigData('api_key');   
        
		$customer_id = $order->getData('customer_id');      
		
		$customer = Mage::getModel('customer/customer')->load($customer_id);
       
		$options = new PurchaseOptions($customer->getEmail());
		if ($transaction_mode == "test") {
			$options->setTestEnabled(true);
		}
		$options->setRedirectUrl($this->getOrderPlaceRedirectUrl());		
		$language = substr(Mage::app()->getLocale()->getLocaleCode(), 0, strpos(Mage::app()->getLocale()->getLocaleCode(), "_")) ;
		$checkout = null ;
        // Create items list        
        foreach( $order->getAllItems() as $items )
        {
        	pblog( round($items->getQtyOrdered()) . "x". $this->getNumberFormat( $items->getPrice()) . " " . $order->getOrderCurrencyCode() . "-" . $items->getName() . "(" . $language . ")" );
        	if ($checkout == null) {
				$checkout = $options->withCheckout()->addItem(CheckoutItem::of($items->getQtyOrdered(), $items->getSku())
										->addName($language, $items->getName())
										->addPrice($order->getOrderCurrencyCode(), $this->getNumberFormat( $items->getPrice()))
										) ;
			}
			else
			{
				$checkout->addItem(CheckoutItem::of($items->getQtyOrdered(), $items->getSku())
										->addName($language, $items->getName())
										->addPrice($order->getOrderCurrencyCode(), $this->getNumberFormat( $items->getPrice()))
										) ;
			}
        }
        if ($order->getShippingAmount() > 0) {
            $checkout->addItem(CheckoutItem::of(1, "SHIPPING")
            						->addName($language, "Shipping")
            						->addPrice($order->getOrderCurrencyCode(), $this->getNumberFormat( $order->getShippingAmount()))
            						) ;     
        }
        $checkout->addTotal($order->getOrderCurrencyCode(), $this->getTotalAmount( $order ));
        pblog( print_r($options, true) );	
        $data = array("apiKey"=>$api_key, "options"=>$options) ;      
/*        $getAmount = file_get_contents("http://www.google.com/finance/converter?a=".$this->getTotalAmount( $order )."&from=".$order->getData('base_currency_code')."&to=EUR"); 

        $getAmount = explode("<span class=bld>",$getAmount);
        $getAmount = explode("</span>",$getAmount[1]);
        $convertedAmount = preg_replace("/[^0-9\.]/", null, $getAmount[0]);
        $calculatedAmount_INR = round($convertedAmount,2);       
*/

      if(PB_DEBUG) {

        Mage::log('purchasedat'.Zend_Debug::dump($data, null, false), null, 'purchasedat.log');

        }
        return( $data );
    }



    public function initialize( $paymentAction, $stateObject )
    {
      
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState( $state );
        $stateObject->setStatus( 'pending_payment' );
        $stateObject->setIsNotified( false );
    }

    public function getResponseOperation($response)
    {       

           $order = Mage::getModel('sales/order');  
    
        $transaction_mode = $this->getConfigData('trans_mode');
        $api_key = $this->getConfigData('api_key');   

        if(isset($response['status']))
        {
           $txnid=$response['txnid'];

           $transaction_mode = $this->getConfigData('trans_mode');

            if($transaction_mode == 'test')
            {
               
                $orderid=$txnid-28800;
            } else
            {
              $orderid=$txnid;
            } 
          
           
           if($response['status']=='success' || $response['status']=='in progress')
            {
                $status=$response['status'];
                $order->loadByIncrementId($orderid);
                $billing = $order->getBillingAddress();
                $amount      = $response['amount'];
                $productinfo = $response['productinfo'];  
                $firstname   = $response['firstname'];
                $email       = $response['email'];
                $keyString='';
                $Udf1 = $response['udf1'];
                $Udf2 = $response['udf2'];
                $Udf3 = $response['udf3'];
                $Udf4 = $response['udf4'];
                $Udf5 = $response['udf5'];
                $Udf6 = $response['udf6'];
                $Udf7 = $response['udf7'];
                $Udf8 = $response['udf8'];
                $Udf9 = $response['udf9'];
                $Udf10 = $response['udf10'];
               
                $keyString =  $api_key.'|'.$txnid.'|'.$amount.'|'.$productinfo.'|'.$firstname.'|'.$email.'|'.$Udf1.'|'.$Udf2.'|'.$Udf3.'|'.$Udf4.'|'.$Udf5.'|'.$Udf6.'|'.$Udf7.'|'.$Udf8.'|'.$Udf9.'|'.$Udf10;    
             
                
                $keyArray = explode("|",$keyString);
                $reverseKeyArray = array_reverse($keyArray);
                $reverseKeyString=implode("|",$reverseKeyArray);
                $sentHashString = strtolower(hash('sha512', $saltString));
                 $responseHashString=$_REQUEST['hash'];
                
                if($sentHashString==$responseHashString)
                {
                     
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                        $order->save();
                        $order->sendNewOrderEmail();
                
                }
                else
                {
                     
                    $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
                    $order->cancel()->save();
                    }
                
                if ($debug_mode==1) {
                    $debugId=$response['udf1'];  
                    $data = array('response_body'=>implode(",",$response));
                    $model = Mage::getModel('purchasedatcheckout/api_debug')->load($debugId)->addData($data);
                    $model->setId($id)->save();
                  }
               }
           
           if($response['status']=='failure')
           {
              
               $order->loadByIncrementId($orderid);
               $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
               // Inventory updated 
               $this->updateInventory($orderid);
               
               $order->cancel()->save();
               
               if ($debug_mode==1) {
                $debugId=$response['udf1'];
                        $data = array('response_body'=>implode(",",$response));
                    $model = Mage::getModel('purchasedatcheckout/api_debug')->load($debugId)->addData($data);
                    $model->setId($id)->save();
                  }
           
           }
           else  if($response['status']=='pending')
           {
                
               $order->loadByIncrementId($orderid);
               $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
               // Inventory updated  
               $this->updateInventory($orderid);
               $order->cancel()->save();
                       
               if ($debug_mode==1) {
                $debugId=$response['udf1'];
                        $data = array('response_body'=>implode(",",$response));
                    $model = Mage::getModel('purchasedatcheckout/api_debug')->load($debugId)->addData($data);
                    $model->setId($id)->save();
                  }
           
           }
           
        }
        else
        {
                   
           $order->loadByIncrementId($response['id']);
           $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
          // Inventory updated 
           $order_id=$response['id'];
           $this->updateInventory($order_id);
           
           $order->cancel()->save();
           
         
        }       

    }

    public function h()
    {
        return;
    }

    public function updateInventory($order_id)
    {
  
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $items = $order->getAllItems();
        foreach ($items as $itemId => $item)
        {
           $ordered_quantity = $item->getQtyToInvoice();
           $sku=$item->getSku();
           $product = Mage::getModel('catalog/product')->load($item->getProductId());
           $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
          
           $updated_inventory=$qtyStock + $ordered_quantity;
                    
           $stockData = $product->getStockItem();
           $stockData->setData('qty',$updated_inventory);
           $stockData->save(); 
            
       } 
    }
  
    public function getPurchasedatUrl()
    {
       
        switch( $this->getConfigData( 'trans_mode' ) )
        {
            case 'test':
                $url = 'https://test.payu.in/_payment';
                break;
            case 'live':
            default :
                $url = 'https://secure.payu.in/_payment';
                break;
        }
        
        return( $url );
    }
    
}