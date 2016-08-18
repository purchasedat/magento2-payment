## Synopsis

An extension to add Purchased.at Payment method on Magento 2.x.

## Installation

This module is intended to be installed using composer.  After including this component and enabling it, you can verify it is installed by going the backend at:
If you can't use composer, follow these steps:
1. Copy the module directories and its files to installed magento 2 directory root
2. Edit file app/etc/config.php, add the new module into modules array ('PurchasedAt_Magento2Payment' => 1,)
3. Add new record into setup_module data table with these fields: module = PurchasedAt_Magento2Payment, schema_version = 2.0.0, data_version = 2.0.0

## Configuration

Go to the Stores/Configuration/Sales/Payment methods page in magento admin.
Find the Purchased.at module section.
You can set the title, the allowed countries, instructions text, and sort order as you wish.
If you want to test the module, set the Test mode to "Test", otherwise set it to "Live".
The API key is required, you can get it from purchased.at vendor page, if you register there, create a project and generate the API key.

Once there check that the module name shows up in the list to confirm that it was installed correctly.

If you don't see the Purchased.at payment option on webshop checkout page payment section, please clear magento cache, after that check it again.