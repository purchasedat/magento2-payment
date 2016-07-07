<?php
/**
 * Itn.php
 *
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */

/**
 * Mage_Paypal_Model_Itn
 */
class Mage_Paypal_Model_Itn
{
    // {{{ getWriteLog()
    /**
     * getWriteLog
     */
	public function getWriteLog( $data )
    {
		$text = "\n";
		$text .= "RESPONSE: From Purchased.at[". date("Y-m-d H:i:s") ."]"."\n";
		
        foreach( $_REQUEST as $key => $val )
			$text .= $key."=>".$val."\n";

		$file = dirname( dirname( __FILE__ ) ) ."/Logs/notify.txt";
		
		$handle = fopen( $file, 'a' );
		fwrite( $handle, $text );
		fclose( $handle );
	}
    // }}}
}