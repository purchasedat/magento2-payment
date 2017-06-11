## purchased-at/magento2-payment

This Magento 2.x module provides a payment method for the Purchased.at service.

## Installation

### Pre-requisites
Make sure your Magento 2.x installation is completed via composer or the web based Setup Wizard. 

### Install using `composer`

1. Log into your Magento 2 server and switch to the Magento filesystem owner user.
2. Change your current directory to the root of your Magento 2 installation.
3. Add the following line to ./composer.json under "require"

  	```json
  	"purchased-at/magento2-payment": "~1.0",
  	```

3. Run `composer update` && `composer install` and the Magento setup procedures:

  	```bash
  	./vendor/composer/composer/bin/composer update && \
  	./vendor/composer/composer/bin/composer install && \
  	./bin/magento setup:upgrade && \
  	./bin/magento setup:di:compile && \
  	./bin/magento cache:flush config && \
  	./bin/magento setup:static-content:deploy
  	```
	> Make sure you use the Magento bundled `composer`. If you would like to run it without `php -f` like above, make sure the binary has `u+x` privileges. Same applies to the `magento` binary, make it `u+x` if needed.

### Install manually

1. Download the latest release from Github: https://github.com/purchased-at/magento2-payment/releases
2. Log into your Magento 2 server and switch to the Magento filesystem owner user.
3. Change to your Magento 2 installation's root directory and create a sub-directory under `app/code/PurchasedAt_Magento2Payment`.
4. Extract the downloaded package to this directory having the `registration.php` file at `app/code/PurchasedAt_Magento2Payment/registration.php`.
5. Edit `app/etc/config.php` and add a new item in the modules array for the new module: 
	```php
	...
	'PurchasedAt_Magento2Payment' => 1,

	```
4. Add a new database record in the `setup_module` table with these fields:
	```SQL
	INSERT INTO `setup_module` (`module`, `schema_version`, `data_version`) VALUES ('PurchasedAt_Magento2Payment', '2.0.0', '2.0.0')

	```

### Install from Magento Connect
Our module submission is being reviewed. You will be able to install it via Magento Connect as soon as it gets approved.

## Configuration
To get the payment module up and running you need to do some configuration that involves setting your Purchased.at API key. So if you do not already have an API key, head first to your [Vendor Dashboard](https://vendor.purchased.at/) and create a project with an API key.
Having your API key prepared:

1. Go to your Magento Admin
2. Navigate to Stores > Configuration > Sales > Payment methods
3. Scroll down to the Purchased.at section
4. Here you can set the title, the allowed countries, instructions text, sort order etc. but **make sure you enter you API key**, it is comoulsory even for testing. 

> If you want to test the module, set the Test mode to "Test" otherwise set it to "Live".

> If you don't see the Purchased.at among payment option at the store's checkout, please clear the Magento cache via the Magento Admin under System > Cache Management > Flush Magento Cache.