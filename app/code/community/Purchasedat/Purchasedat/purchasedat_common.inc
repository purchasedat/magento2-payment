<?php
/**
 * purchasedat_common.inc
 *
 * 
 * @author     Árpád Tóth
 * @category   Purchasedat
 * @package    Purchasedat
 */

//// Create user agent string
// User agent constituents (for cURL)
define( 'PB_SOFTWARE_NAME', 'Magento' );
define( 'PB_SOFTWARE_VER', Mage::getVersion() );
define( 'PB_MODULE_NAME', 'Purchasedat-Magento' );
define( 'PB_MODULE_VER', '1.0' );

// Features
// - PHP
$pbFeatures = 'PHP '. phpversion() .';';

function pblog( $msg = '', $close = false )
{
    static $fh = 0;
    global $module;

    // Only log if debugging is enabled
    if( PB_DEBUG )
    {
        if( $close )
        {
            fclose( $fh );
        }
        else
        {
            // If file doesn't exist, create it
            if( !$fh )
            {
                $pathinfo = pathinfo( __FILE__ );
                $fh = fopen( $pathinfo['dirname'] .'/purchasedat.log', 'a+' );
            }

            // If file was successfully created
            if( $fh )
            {
                $line = date( 'Y-m-d H:i:s' ) .' : '. $msg ."\n";

                fwrite( $fh, $line );
            }
        }
    }
}

function pbGetData()
{
    // Posted variables from ITN
    $pbData = $_POST;

    // Strip any slashes in data
    foreach( $pbData as $key => $val )
        $pbData[$key] = stripslashes( $val );

    // Return "false" if no data was received
    if( sizeof( $pbData ) == 0 )
        return( false );
    else
        return( $pbData );
}
?>
