<?php

/**

purchased.at PHP SDK - see https://github.com/purchased-at/sdk-php for more information

The MIT License (MIT)

Copyright (c) 2016 purchased.at GmbH

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.README.md


ACKNOWLEDGEMENTS:

JWT:

Copyright (c) 2011, Neuman Vong

All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above
copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided
with the distribution.

* Neither the name of Neuman Vong nor the names of other
contributors may be used to endorse or promote products derived
from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Signing {
use DomainException;
use UnexpectedValueException;
/**
 * JSON Web Token implementation
 *
 * Minimum implementation used by Realtime auth, based on this spec:
 * http://self-issued.info/docs/draft-jones-json-web-token-01.html.
 *
 * Copied and adapted from https://github.com/progrium/php-jwt/blob/master/JWT.php
 *
 * @author Neuman Vong <neuman@twilio.com>
 * @see https://github.com/progrium/php-jwt/blob/master/JWT.php
 */
class JWT
{
    /**
     * @param string      $jwt    The JWT
     * @param string|null $key    The secret key
     * @param bool        $verify Don't skip verification process
     *
     * @return object The JWT's payload as a PHP object
     *
     * @throws DomainException thrown when $verify is true and no algorithm is present in the header
     * @throws UnexpectedValueException thrown on any error while decoding
     */
    public static function decode($jwt, $key = null, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $payloadb64, $cryptob64) = $tks;
        if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))) {
            throw new UnexpectedValueException('Invalid segment encoding');
        }
        if (null === ($payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadb64)))) {
            throw new UnexpectedValueException('Invalid segment encoding');
        }
        $sig = JWT::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new DomainException('Empty algorithm');
            }
            if ($sig != JWT::sign("{$headb64}.{$payloadb64}", $key, $header->alg)) {
                throw new UnexpectedValueException('Signature verification failed');
            }
        }
        return $payload;
    }
    /**
     * @param object|array $payload   PHP object or array
     * @param string       $key       The secret key
     * @param string       $kid       The key id
     * @param string       $algorithm The signing algorithm
     *
     * @return string A JWT
     */
    public static function encode($payload, $key, $kid = null, $algorithm = 'HS256')
    {
        $header = array('typ' => 'JWT', 'alg' => $algorithm, 'kid' => $kid);
        $segments = array();
        $segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($header));
        $segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature = JWT::sign($signing_input, $key, $algorithm);
        $segments[] = JWT::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }
    /**
     * @param string $msg    The message to sign
     * @param string $key    The secret key
     * @param string $method The signing algorithm
     *
     * @return string An encrypted message
     */
    public static function sign($msg, $key, $method = 'HS256')
    {
        $methods = array('HS256' => 'sha256', 'HS384' => 'sha384', 'HS512' => 'sha512');
        if (empty($methods[$method])) {
            throw new DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }
    /**
     * @param string $input JSON string
     *
     * @return object Object representation of JSON string
     */
    public static function jsonDecode($input)
    {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && ($errno = json_last_error())) {
            JWT::handleJsonError($errno);
        } else {
            if ($obj === null && $input !== 'null') {
                throw new DomainException('Null result with non-null input');
            }
        }
        return $obj;
    }
    /**
     * @param object|array $input A PHP object or array
     *
     * @return string JSON representation of the PHP object or array
     */
    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && ($errno = json_last_error())) {
            JWT::handleJsonError($errno);
        } else {
            if ($json === 'null' && $input !== null) {
                throw new DomainException('Null result with non-null input');
            }
        }
        return $json;
    }
    /**
     * @param string $input A base64 encoded string
     *
     * @return string A decoded string
     */
    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    /**
     * @param string $input Anything really
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    /**
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     */
    private static function handleJsonError($errno)
    {
        $messages = array(JSON_ERROR_DEPTH => 'Maximum stack depth exceeded', JSON_ERROR_CTRL_CHAR => 'Unexpected control character found', JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON');
        throw new DomainException(isset($messages[$errno]) ? $messages[$errno] : 'Unknown JSON error: ' . $errno);
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Signing {
class JWTOptions
{
    /** @var string */
    private $jwtId;
    /** @var \DateTime */
    private $notBefore;
    /** @var \DateTime */
    private $expiration;
    /** @return string */
    public function getJwtId()
    {
        return $this->jwtId;
    }
    /** @param string $jwtId */
    public function setJwtId($jwtId)
    {
        $this->jwtId = $jwtId;
        return $this;
    }
    /** @return \DateTime */
    public function getNotBefore()
    {
        return $this->notBefore;
    }
    /** @param \DateTime $notBefore */
    public function setNotBefore($notBefore)
    {
        $this->notBefore = $notBefore;
        return $this;
    }
    /** @return \DateTime */
    public function getExpiration()
    {
        return $this->expiration;
    }
    /** @param \DateTime $expiration */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt {
class Constants
{
    const SDK_VERSION = "0.1";
    const SIGNATURE_HEADER = "HTTP_X_PAT_SIGNATURE";
    const DEFAULT_WIDGET_URL = 'https://widget.purchased.at/widget/v1/js';
    const DEFAULT_JWT_NOT_BEFORE_INTERVAL = 'PT5M';
    const DEFAULT_JWT_EXPIRATION = '30 minutes';
    const DEFAULT_API_ENDPOINT = 'https://payment.purchased.at';
    const DEFAULT_API_VERIFY_SIGNATURE = true;
    const DEFAULT_API_VERIFY_TIMESTAMP_MAX_AGE = -1;
    const NOTIFICATION_OK_RESULT = '"OK"';
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Customer
{
    /** @var string */
    private $email;
    /** @var string */
    private $externalId;
    public function build()
    {
        return array('email' => $this->email, 'external_id' => $this->externalId);
    }
    /** @return string */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @param string $email Customer email address (mandatory).
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }
    /**
     * @param string $externalId User provided customer id (not used by purchased.at).
     * @return Customer
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Payload
{
    /** @var Customer */
    private $customer;
    /** @var Transaction */
    private $transaction;
    /** @var Select */
    private $select;
    /** @var Checkout */
    private $checkout;
    /** @var Response */
    private $response;
    /** @var Test */
    private $test;
    /** @var string */
    private $sdk;
    public function __construct()
    {
        $this->customer = new Customer();
        $this->transaction = new Transaction();
        $this->select = new Select();
        $this->checkout = null;
        $this->response = new Response();
        $this->test = new Test();
    }
    public function build()
    {
        if ($this->checkout != null) {
            $this->select = null;
        }
        return array('customer' => $this->customer->build(), 'transaction' => $this->transaction->build(), 'select' => !is_null($this->select) ? $this->select->build() : null, 'checkout' => !is_null($this->checkout) ? $this->checkout->build() : null, 'response' => $this->response->build(), 'test' => $this->test->build(), 'sdk' => $this->sdk);
    }
    /** @return Customer */
    public function getCustomer()
    {
        return $this->customer;
    }
    /**
     * @param Customer $customer
     * @return Payload
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }
    /** @return Test */
    public function getTest()
    {
        return $this->test;
    }
    /**
     * @param Test $test
     * @return Payload
     */
    public function setTest($test)
    {
        $this->test = $test;
        return $this;
    }
    /** @return Transaction */
    public function getTransaction()
    {
        return $this->transaction;
    }
    /**
     * @param Transaction $transaction
     * @return Payload
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }
    /** @return Select */
    public function getSelect()
    {
        return $this->select;
    }
    /**
     * @param Select $select
     * @return Payload
     */
    public function setSelect($select)
    {
        $this->select = $select;
        return $this;
    }
    /**
     * @return Checkout
     */
    public function getCheckout()
    {
        return $this->checkout;
    }
    /**
     * @param Checkout $checkout
     * @return Payload
     */
    public function setCheckout($checkout)
    {
        $this->checkout = $checkout;
        return $this;
    }
    /** @return Response */
    public function getResponse()
    {
        return $this->response;
    }
    /**
     * @param Response $response
     * @return Payload
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
    /** @return string */
    public function getSdk()
    {
        return $this->sdk;
    }
    /**
     * @param string $sdk
     * @return Payload
     */
    public function setSdk($sdk)
    {
        $this->sdk = $sdk;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Response
{
    /** @var string */
    private $redirect;
    /** @var string */
    private $notification;
    /** @var bool */
    private $email;
    public function build()
    {
        return array('redirect' => $this->redirect, 'notification' => $this->notification, 'email' => $this->email);
    }
    /** @return string */
    public function getRedirect()
    {
        return $this->redirect;
    }
    /**
     * @param string $redirect The URL to redirect after completed payment.
     * @return Response
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
        return $this;
    }
    /** @return string */
    public function getNotification()
    {
        return $this->notification;
    }
    /**
     * @param string $notification The API URL to post transaction status changes to.
     * @return Response
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }
    /** @return bool */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @param bool $email Send email to vendor provided address after successful payment.
     * @return Response
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Select
{
    /** @var string */
    private $item;
    public function build()
    {
        return array('item' => $this->item);
    }
    /** @return string */
    public function getItem()
    {
        return $this->item;
    }
    /**
     * @param string $item Preselect the specified item in purchase dialog.
     * @return Select
     */
    public function setItem($item)
    {
        $this->item = $item;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Test
{
    const REVISION_LATEST = 'latest';
    const REVISION_IN_REVIEW = 'in_review';
    const REVISION_PUBLISHED = 'published';
    /** @var bool */
    private $enabled = false;
    /** @var string */
    private $country;
    /** @var string */
    private $currency;
    /** @var array */
    private $languages;
    /** @var string */
    private $revisionMode;
    public function build()
    {
        return array('enabled' => $this->enabled, 'country' => $this->country, 'currency' => $this->currency, 'languages' => $this->languages, 'revision_mode' => $this->revisionMode);
    }
    /** @return bool */
    public function getEnabled()
    {
        return $this->enabled;
    }
    /**
     * @param bool $enabled Whether test mode is enabled. All other test related settings are ignored if test mode is false.
     * @return Test
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }
    /** @return string */
    public function getCountry()
    {
        return $this->country;
    }
    /**
     * @param string $country ISO3166 2 letter country code (US,DE,..)
     * @return Test
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }
    /** @return string */
    public function getCurrency()
    {
        return $this->currency;
    }
    /**
     * @param string $currency ISO4271 3 letter currency code (USD,EUR,..)
     * @return Test
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
    /** @return array */
    public function getLanguages()
    {
        return $this->languages;
    }
    /**
     * @param array $languages ISO639 2 letter language codes (en,de,..)
     * @return Test
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
        return $this;
    }
    /**
     * @return string
     *
     * @see self::REVISION_*
     */
    public function getRevisionMode()
    {
        return $this->revisionMode;
    }
    /**
     * Controls which version of items are displayed. Use Test::REVISION_* constants for values.
     *
     * @param string $revisionMode
     *
     * @see self::REVISION_*
     * @return Test
     */
    public function setRevisionMode($revisionMode)
    {
        $validValues = array(self::REVISION_PUBLISHED, self::REVISION_LATEST, self::REVISION_IN_REVIEW);
        if ($revisionMode && !in_array($revisionMode, $validValues)) {
            throw new \InvalidArgumentException($revisionMode . ' is not a valid revision mode for testing. Please use one of ' . implode(', ', $validValues) . '.');
        }
        $this->revisionMode = $revisionMode;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Transaction
{
    /**
     * The transaction has been started but the customer is still in the payment process.
     */
    const STATE_PROCESSING = 'processing';
    /**
     * The transaction is waiting for authentication or final payment (e.g. by the bank).
     */
    const STATE_PENDING = 'pending';
    /**
     * The request succeeded, the goods can be delivered (this state is not necessarily final).
     */
    const STATE_SUCCESSFUL = 'successful';
    /**
     * The transaction has failed and no goods should be delivered (in some limited cases the state may not be final).
     */
    const STATE_FAILED = 'failed';
    /**
     * The customer has asked for a chargeback, the goods should no longer be delivered.
     */
    const STATE_CHARGEBACK = 'chargeback';
    /**
     * A refund for the customer was requested and the money was sent back. (Some payment providers only)
     */
    const STATE_REFUND = 'refund';
    /** @var string */
    private $externalId;
    public function build()
    {
        return array('external_id' => $this->externalId);
    }
    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }
    /**
     * @param string $externalId User provided transaction id. Not used by purchased.at.
     * @return Transaction
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
}
}

/** This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at */
namespace PurchasedAt\Purchase {
class CheckoutItem
{
    /** @var int Count of items sold. */
    private $count;
    /** @var array[string]string ISO2 language code => localized name. Name of this item in multiple languages */
    private $name;
    /** @var string Stock keeping unit (SKU) of this item. */
    private $sku;
    /** @var string External identifier of this item. */
    private $externalId;
    /** @var array[string]string Price of one unit of this item. */
    private $price;
    /**
     * CheckoutItem constructor.
     * @param int $count Count of items sold.
     * @param array $name ISO2 language code => localized name. Name of this item in multiple languages
     * @param string $sku Stock keeping unit (SKU) of this item.
     * @param array $price Price of one unit of this item.
     * @param string|null $externalId External identifier of this item (optional).
     */
    public function __construct($count, $sku, array $name = array(), array $price = array(), $externalId = null)
    {
        $this->count = $count;
        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->externalId = $externalId;
    }
    public function build()
    {
        return array('count' => $this->count, 'name' => (object) $this->name, 'sku' => $this->sku, 'external_id' => $this->externalId, 'price' => (object) $this->price);
    }
    /** @return int */
    public function getCount()
    {
        return $this->count;
    }
    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
    /** @return array[string]string */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param array [string]string $name
     * @return $this
     */
    public function setName(array $name)
    {
        $this->name = $name;
        return $this;
    }
    /** @return string */
    public function getSku()
    {
        return $this->sku;
    }
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }
    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }
    /**
     * @param string $externalId
     * @return $this
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
    /** @return array[string]string */
    public function getPrice()
    {
        return $this->price;
    }
    /**
     * @param array [string]string $price
     * @return $this
     */
    public function setPrice(array $price)
    {
        $this->price = $price;
        return $this;
    }
    // builder
    /**
     * @param $count int
     * @param $sku string
     * @param null|string $lang
     * @param null|string $name
     * @param null|string $currency
     * @param null|string $price
     * @return CheckoutItem
     */
    public static function of($count, $sku, $lang = null, $name = null, $currency = null, $price = null)
    {
        $names = array();
        if ($lang !== null && $name === null || $lang === null && $name !== null) {
            throw new \InvalidArgumentException("if \$lang is given \$name must also be given and vice versa");
        } else {
            $names[$lang] = $name;
        }
        $prices = array();
        if ($currency !== null && $price === null || $currency === null && $price !== null) {
            throw new \InvalidArgumentException("if \$currency is given \$price must also be given and vice versa");
        } else {
            $prices[$currency] = $price;
        }
        return new CheckoutItem($count, $sku, $names, $prices);
    }
    /**
     * @param $lang string ISO2 language code
     * @param $name string name of item in specified language
     * @return $this
     */
    public function addName($lang, $name)
    {
        $this->name[$lang] = $name;
        return $this;
    }
    /**
     * @param $currency string ISO3 currency code
     * @param $price string price ofe item in specified currency
     * @return $this
     */
    public function addPrice($currency, $price)
    {
        $this->price[$currency] = $price;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\Purchase {
class Checkout
{
    /** @var array[string]string currency=>amount array of total price of items sold by this checkout. */
    private $total;
    /** @var CheckoutItem[] items sold in by this checkout. */
    private $items;
    public function build()
    {
        return array('total' => (object) $this->total, 'items' => array_map(function ($item) {
            return $item->build();
        }, $this->items));
    }
    /** @return array[string]string */
    public function getTotal()
    {
        return $this->total;
    }
    /**
     * @param array [string]string $total
     * @return $this
     */
    public function setTotal(array $total)
    {
        $this->total = $total;
        return $this;
    }
    /** @return CheckoutItem[] */
    public function getItems()
    {
        return $this->items;
    }
    /**
     * @param CheckoutItem[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }
    // build helper
    /**
     * Set the total value of all items (sum of all items: $item->count * $item->price).
     *
     * @param $currency string ISO3 currency code
     * @param $total string total value of all goods in $currency
     * @return $this
     */
    public function addTotal($currency, $total)
    {
        $this->total[$currency] = $total;
        return $this;
    }
    /**
     * Adds an item to the list of checkout items.
     *
     * @param $item CheckoutItem the item to add
     * @return $this
     */
    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt {
use PurchasedAt\Signing\JWT;
use PurchasedAt\Signing\JWTOptions;
/**
 * This class creates a HTML embed code and thus helps with the first step of creating a purchase.
 *
 * Example:
 *
 * ```
 * $purchaseOptions = new PurchasedAt\PurchaseOptions($customerEmail);
 *
 * echo PurchasedAt\PurchaseScript::render($apiKey, $purchaseOptions);
 * ```
 */
class PurchaseScript
{
    /**
     * This function renders a HTML embed code required for creating a purchase.
     *
     * Example:
     *
     * ```
     * $purchaseOptions = new PurchasedAt\PurchaseOptions($customerEmail);
     *
     * echo PurchasedAt\PurchaseScript::render($apiKey, $purchaseOptions);
     * ```
     *
     * @param string          $apiKey          the api key
     * @param PurchaseOptions $purchaseOptions the purchase options
     * @param string          $target          id of target dom element
     * @param JWTOptions      $jwtOptions      the jwt options
     *
     * @return string HTML snippet to initialize the purchased.at widget
     *
     * @throws \InvalidArgumentException if an invalid type was provided to any of the parameters.
     */
    public static function render($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
    {
        $token = self::token($apiKey, $purchaseOptions, $jwtOptions);
        $widgetUrl = $purchaseOptions->getWidgetUrl();
        if (!isset($target) || !$target) {
            $target = null;
        }
        $params = json_encode(array('token' => $token));
        if (json_last_error()) {
            throw new \InvalidArgumentException('The JWT options parameter to PurchaseScript needs to be JSON encodeable. ' . json_last_error_msg());
        }
        $target = json_encode($target);
        if (json_last_error()) {
            throw new \InvalidArgumentException('The token parameter to PurchaseScript needs to be JSON encodeable. ' . json_last_error_msg());
        }
        return '<script src="' . htmlspecialchars($widgetUrl) . '"></script>
                <script>
                        purchased_at.auto(' . $params . ',' . $target . ');
                </script>';
    }
    /**
     * This function generates the required JWT token to create a HTML embed code required for creating a purchase.
     *
     * Example:
     *
     * ```
     * $purchaseOptions = new PurchasedAt\PurchaseOptions($customerEmail);
     *
     * echo PurchasedAt\PurchaseScript::token($apiKey, $purchaseOptions);
     * ```
     *
     * @param string          $apiKey          the api key
     * @param PurchaseOptions $purchaseOptions the purchase options
     * @param JWTOptions      $jwtOptions      the jwt options
     *
     * @return string JWT token
     *
     * @throws \InvalidArgumentException if an invalid type was provided to any of the parameters.
     */
    public static function token($apiKey, $purchaseOptions, $jwtOptions = null)
    {
        if (!$purchaseOptions instanceof PurchaseOptions) {
            if (is_object($purchaseOptions)) {
                $type = get_class($purchaseOptions);
            } else {
                $type = gettype($purchaseOptions);
            }
            throw new \InvalidArgumentException('Invalid parameter type ' . $type . ' for purchaseOptions in PurchaseScript::render. ' . 'Please use an instance of the PurchaseOptions class instead.');
        }
        if (!is_null($jwtOptions) && !$jwtOptions instanceof JWTOptions) {
            if (is_object($jwtOptions)) {
                $type = get_class($jwtOptions);
            } else {
                $type = gettype($jwtOptions);
            }
            throw new \InvalidArgumentException('Invalid parameter type ' . $type . ' for jwtOptions in PurchaseScript::render. Please use an instance of the JWTOptions class instead.');
        }
        $parts = explode(':', $apiKey);
        if (count($parts) != 2 || strlen($parts[0]) < 1 || strlen($parts[1]) < 1) {
            throw new \InvalidArgumentException('Invalid API key (must contain exactly two parts separated by a colon)');
        }
        $key = $parts[1];
        $keyId = $parts[0];
        $utc = new \DateTimeZone('UTC');
        if (!isset($jwtOptions)) {
            $jwtOptions = new JWTOptions();
        }
        if (!$jwtOptions->getJwtId()) {
            $jwtOptions->setJwtId(base64_encode(mt_rand()));
        }
        if (!$jwtOptions->getNotBefore()) {
            $jwtOptions->setNotBefore((new \DateTime('now', $utc))->sub(new \DateInterval(Constants::DEFAULT_JWT_NOT_BEFORE_INTERVAL)));
        }
        if (!$jwtOptions->getExpiration()) {
            $jwtOptions->setExpiration(new \DateTime(Constants::DEFAULT_JWT_EXPIRATION, $utc));
        }
        $payload = $purchaseOptions->getPayload();
        if (!$payload->getCustomer()->getEmail()) {
            throw new \InvalidArgumentException('The purchaseOptions parameter must contain the customers e-mail address. (Empty string found.)');
        }
        if (!$payload->getSdk()) {
            $payload->setSdk('PHP/' . Constants::SDK_VERSION);
        }
        return JWT::encode(array('jti' => $jwtOptions->getJwtId(), 'aud' => 'pat-api', 'nbf' => $jwtOptions->getNotBefore()->getTimestamp(), 'exp' => $jwtOptions->getExpiration()->getTimestamp(), 'pat:payload' => $payload->build()), $key, $keyId);
    }
}
}

/**
 * This code is part of the purchased.at client SDK.
 *
 * @see https://docs.purchased.at
 */
namespace PurchasedAt {
use PurchasedAt\Purchase\Checkout;
use PurchasedAt\Purchase\Test;
/**
 * This class allows for setting various purchase options. The minimum you need to provide is the customer's e-mail
 * address.
 */
class PurchaseOptions
{
    const REVISION_LATEST = Test::REVISION_LATEST;
    const REVISION_IN_REVIEW = Test::REVISION_IN_REVIEW;
    const REVISION_PUBLISHED = Test::REVISION_PUBLISHED;
    /**
     * @var string
     */
    private $widgetUrl;
    /**
     * @var Purchase\Payload
     */
    private $payload;
    /**
     * Initialize the purchase options. By default this requires a customer e-mail address as a parameter.
     *
     * @param string $customerEmail
     */
    public function __construct($customerEmail)
    {
        $this->widgetUrl = Constants::DEFAULT_WIDGET_URL;
        $this->payload = new Purchase\Payload();
        $this->payload->getCustomer()->setEmail($customerEmail);
    }
    /**
     * @return Purchase\Payload
     */
    public function getPayload()
    {
        return $this->payload;
    }
    /**
     * Override the payload in this options class.
     *
     * @param Purchase\Payload $payload
     * @return PurchaseOptions
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }
    /**
     * Returns the URL for rendering the widget.
     *
     * @return string
     */
    public function getWidgetUrl()
    {
        return $this->widgetUrl;
    }
    /**
     * Override the URL for rendering the widget.
     *
     * @param string $widgetUrl
     * @return PurchaseOptions
     */
    public function setWidgetUrl($widgetUrl)
    {
        $this->widgetUrl = $widgetUrl;
        return $this;
    }
    // convenience functions
    /**
     * Change the customer e-mail address.
     *
     * @param string $customerEmail
     * @return PurchaseOptions
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->payload->getCustomer()->setEmail($customerEmail);
        return $this;
    }
    /**
     * Set an ID that references the customer in the vendor (i.e. your) database. This can be used to identify the
     * customer later in the process.
     *
     * @param string $customerExternalId
     * @return PurchaseOptions
     */
    public function setCustomerExternalId($customerExternalId)
    {
        $this->payload->getCustomer()->setExternalId($customerExternalId);
        return $this;
    }
    /**
     * Set an ID that references the transaction in the vendor (i.e. your) database. This can be used to identify the
     * transaction later in the process.
     *
     * @param string $transactionExternalId
     * @return PurchaseOptions
     */
    public function setTransactionExternalId($transactionExternalId)
    {
        $this->payload->getTransaction()->setExternalId($transactionExternalId);
        return $this;
    }
    /**
     * Preselect the specified item in purchase dialog.
     *
     * @param string $selectItem
     * @return PurchaseOptions
     */
    public function setSelectItem($selectItem)
    {
        $this->payload->getSelect()->setItem($selectItem);
        return $this;
    }
    /**
     * Set the URL to redirect the customer to after the transaction is completed.
     *
     * @param string $redirectUrl
     * @return PurchaseOptions
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->payload->getResponse()->setRedirect($redirectUrl);
        return $this;
    }
    /**
     * Set the URL where a notification of the purchase should be sent to.
     *
     * @param string $notificationUrl
     *
     * @see https://docs.purchased.at/display/PUR/PHP+Notifications+Guide
     * @return PurchaseOptions
     */
    public function setNotificationUrl($notificationUrl)
    {
        $this->payload->getResponse()->setNotification($notificationUrl);
        return $this;
    }
    public function setResponseEmail($responseEmail)
    {
        $this->payload->getResponse()->setEmail($responseEmail);
        return $this;
    }
    /**
     * Enable/disable test mode
     *
     * @param bool $testEnabled
     * @return PurchaseOptions
     */
    public function setTestEnabled($testEnabled)
    {
        $this->payload->getTest()->setEnabled($testEnabled);
        return $this;
    }
    /**
     * Set the country for test mode according to ISO3166-2
     *
     * @param string $testCountry ISO3166-2 country code
     *
     * @see https://en.wikipedia.org/wiki/ISO_3166-2
     * @return PurchaseOptions
     */
    public function setTestCountry($testCountry)
    {
        $this->payload->getTest()->setCountry($testCountry);
        return $this;
    }
    /**
     * Set ISO4271 currency code for test mode (USD, EUR, etc)
     *
     * @param string $testCurrency ISO4271 3 letter currency code (USD,EUR,..)
     *
     * @see https://en.wikipedia.org/wiki/ISO_4217
     * @return PurchaseOptions
     */
    public function setTestCurrency($testCurrency)
    {
        $this->payload->getTest()->setCurrency($testCurrency);
        return $this;
    }
    /**
     * Set multiple ISO639-1 language codes for test mode.
     *
     * @param array $testLanguages ISO639-1 language codes
     *
     * @see https://en.wikipedia.org/wiki/ISO_639-1
     * @return PurchaseOptions
     */
    public function setTestLanguages($testLanguages)
    {
        $this->payload->getTest()->setLanguages($testLanguages);
        return $this;
    }
    /**
     * Set a single ISO639-1 language code for test mode.
     *
     * @param array $language ISO639-1 language code
     *
     * @see https://en.wikipedia.org/wiki/ISO_639-1
     * @return PurchaseOptions
     */
    public function setTestLanguage($language)
    {
        $this->payload->getTest()->setLanguages(array($language));
        return $this;
    }
    /**
     * Controls which version of items are displayed. Use PurchaseOptions::REVISION_* constants for values.
     *
     * @param string $testRevisionMode
     *
     * @see self::REVISION_*
     * @return PurchaseOptions
     */
    public function setTestRevisionMode($testRevisionMode)
    {
        $this->payload->getTest()->setRevisionMode($testRevisionMode);
        return $this;
    }
    /**
     * Creates a checkout transaction.
     * @return Checkout
     */
    public function withCheckout()
    {
        if ($this->payload->getSelect() !== null && $this->payload->getSelect()->getItem() !== null) {
            throw new \LogicException('cannot activate checkout and $select->$item at the same time.');
        }
        $this->payload->setSelect(null);
        $checkout = new Checkout();
        $this->payload->setCheckout($checkout);
        return $checkout;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt {
use PurchasedAt\API\RedirectData;
/**
 * This class is main API client. However, before you start using this, please familiarize yourself with the process:
 *
 * 1. Embed the HTML button code to your checkout page. The easiest way is to use the `PurchasedAt\renderScript()`
 *    function. (Or `PurchaseScript::render()` if you prefer the OO style.)
 * 2. The customer clicks the button and completes the purchased.
 * 3. You receive a request/postback/notification about the purchase. Use APIClient::parseTransactionNotification() for
 *    this.
 * 4. The customer returns to your redirect page, where you process the return using APIClient::parseRedirect().
 */
class APIClient
{
    /**
     * URL of the server to use for API request.
     *
     * @var string
     */
    private $apiEndpoint;
    /**
     * If multiple $apiKeys are present, this defines the one used to make requests.
     *
     * @var string
     */
    private $apiKeyId;
    /**
     * The list of API keys known to this client.
     * Array-keys are the key ids and values the secrets.
     *
     * @var array
     */
    private $apiKeys = array();
    /**
     * If set to true, the signature of the responses of the server will be checked.
     *
     * @var boolean
     */
    private $verifySignature;
    /**
     * Maximum age of the response timestamp in milliseconds.
     * If <= -1 timestamp verification is disabled.
     * If you can't guarantee that your PHP server has a reasonably accurate time set you can disable the timestamp
     * verification.
     *
     * @var integer
     */
    private $verifyTimestampMaxAgeMillis;
    /**
     * APIClient constructor.
     *
     * @param string $apiKey the default API key to use with this client.
     */
    public function __construct($apiKey = null)
    {
        $this->apiEndpoint = Constants::DEFAULT_API_ENDPOINT;
        $this->verifySignature = Constants::DEFAULT_API_VERIFY_SIGNATURE;
        $this->verifyTimestampMaxAgeMillis = Constants::DEFAULT_API_VERIFY_TIMESTAMP_MAX_AGE;
        if (isset($apiKey)) {
            $this->apiKeyId = $this->addApiKey($apiKey);
        }
    }
    /**
     * @return RedirectData parsers the parameters sent by purchased.at for a redirect call.
     * @link https://docs.purchased.at/display/PUR/Payment+Widget#PaymentWidget-redirects
     */
    public function parseRedirect()
    {
        return RedirectData::fromRequest();
    }
    /**
     * Fetches information about a transaction by using the data available in a redirect.
     *
     * @see APIClient::fetchTransaction
     *
     * @param \PurchasedAt\API\RedirectData $redirectData redirect data to use, if null, attempts to parse redirect
     *                                                    data from request.
     *
     * @return APIResult {@link ApiResult::response} contains transaction information if the request was
     *                   successful.
     */
    public function fetchTransactionForRedirect($redirectData = null)
    {
        if ($redirectData === null) {
            $redirectData = $this->parseRedirect();
        }
        if ($redirectData->getTransactionId() === null) {
            return new APIResult(false, 'transaction_missing', null);
        }
        return $this->fetchTransaction($redirectData->getTransactionId());
    }
    /**
     * Fetches information about a transaction.
     *
     * @param string $transactionId server defined transaction id (UUID).
     *
     * @return APIResult {@link ApiResult::response} contains transaction information if the request was successful.
     */
    public function fetchTransaction($transactionId)
    {
        /** @var APIResult result */
        $result = $this->execGet("/api/vendor/transaction/{$transactionId}");
        if (!$result->success) {
            return $result;
        }
        $result->result = API\Transaction::fromJson($result->rawResponse->jsonBody);
        return $result;
    }
    /**
     * Parse an incoming transaction notification.
     *
     * @param string $requestBody     the request body
     * @param string $signatureHeader the value of the signature header
     *
     * @return APIResult the parsed transaction notification wrapped in an APIResult or error information
     */
    public function parseTransactionNotification($requestBody, $signatureHeader)
    {
        $json = json_decode($requestBody, false, 512, JSON_BIGINT_AS_STRING);
        $rawResponse = new APIRequest($requestBody, $json, $signatureHeader);
        $signatureResponse = $this->verifySignature($requestBody, $rawResponse);
        if ($signatureResponse !== null) {
            return $signatureResponse;
        }
        $timestampResponse = $this->verifyTimestamp($rawResponse);
        if ($timestampResponse !== null) {
            return $timestampResponse;
        }
        if ($json->type !== "notification/transaction") {
            return new APIResult(false, "body_invalid_type", $rawResponse);
        }
        $result = new APIResult(true, null, $rawResponse);
        $result->result = API\TransactionNotification::fromJson($json);
        return $result;
    }
    /**
     * Parse an incoming transaction notification and read the data from the request data.
     *
     * @see APIClient::parseTransactionNotification
     * @return APIResult the parsed transaction notification wrapped in an APIResult or error information
     */
    public function parseTransactionNotificationForRequest()
    {
        $requestBody = file_get_contents("php://input");
        $signatureHeader = array_key_exists("HTTP_X_PAT_SIGNATURE", $_SERVER) ? $_SERVER["HTTP_X_PAT_SIGNATURE"] : null;
        return $this->parseTransactionNotification($requestBody, $signatureHeader);
    }
    /**
     * Respond to the purchased.at server after receiving a transaction notification.
     */
    public function acknowledgeTransactionNotification()
    {
        echo Constants::NOTIFICATION_OK_RESULT;
    }
    /**
     * Add a new API key to this client.
     *
     * @param string $apiKey the API key to add
     *
     * @return string the key id part of the API key.
     * @throws \Exception when API key is wrong
     */
    public function addApiKey($apiKey)
    {
        if (strstr($apiKey, ":") === false) {
            throw new \Exception('invalid api key');
        }
        $apiKeyParts = explode(':', $apiKey);
        if (count($apiKeyParts) != 2 || strlen($apiKeyParts[0]) < 1 || strlen($apiKeyParts[1]) < 1) {
            throw new \Exception('invalid api key');
        }
        $this->apiKeys[$apiKeyParts[0]] = $apiKeyParts[1];
        return $apiKeyParts[0];
    }
    private function execGet($path)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $apiKeyId = $this->assertApiKeyId();
        $apiKeySecret = $this->assertApiKeySecret($apiKeyId);
        // the authorization header must be set so purchased.at can verify that the request came from an authorized party
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Pat-Authorization: " . $apiKeyId . ":" . hash_hmac("sha512", $path, $apiKeySecret), 'X-Pat-SDK: PHP/' . Constants::SDK_VERSION));
        $response = curl_exec($curl);
        if ($response === false) {
            $errorNumber = curl_errno($curl);
            return new APIResult(false, 'curl_error_' . $errorNumber, null);
        }
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // class preloader workaround
        $new_line = chr(0xd) . chr(0xa);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $headers = substr($headers, strpos($headers, $new_line) + 2);
        $body = substr($response, $header_size);
        curl_close($curl);
        $headers_ = array();
        foreach (explode($new_line, $headers) as $header) {
            $headerParts = explode(": ", $header, 2);
            $name = $headerParts[0];
            $value = isset($headerParts[1]) ? $headerParts[1] : null;
            if (!isset($name) || $name == "") {
                continue;
            }
            $headers_[$name] = $value;
        }
        $headers = $headers_;
        $signature = $headers["X-Pat-Signature"];
        $jsonBody = json_decode($body, false, 512, JSON_BIGINT_AS_STRING);
        $rawResponse = new APIResponse($status, $headers, $body, $jsonBody, $signature);
        $signatureResponse = $this->verifySignature($body, $rawResponse);
        if ($signatureResponse !== null) {
            return $signatureResponse;
        }
        $timestampResponse = $this->verifyTimestamp($rawResponse);
        if ($timestampResponse !== null) {
            return $timestampResponse;
        }
        if ($rawResponse->status / 100 != 2) {
            return new APIResult(false, APIClient::errorCodeForStatus($rawResponse->status), $rawResponse);
        }
        return new APIResult(true, null, $rawResponse);
    }
    /**
     * @param string $signatureHeader
     *
     * @return array
     */
    private function parseSignatureHeader($signatureHeader)
    {
        $signatureParts = explode(":", $signatureHeader, 2);
        $keyId = $signatureParts[0];
        $signature = $signatureParts[1];
        $secret = $this->apiKeys[$keyId];
        return array($keyId, $signature, $secret);
    }
    /**
     * @param string                 $signatureData
     * @param APIRequest|APIResponse $rawResponse
     *
     * @return null|APIResult
     */
    private function verifySignature($signatureData, $rawResponse)
    {
        if (!$this->verifySignature) {
            return null;
        }
        if (!isset($rawResponse->signature) || $rawResponse->signature == "") {
            return new APIResult(false, 'signature_header_missing', $rawResponse);
        }
        if (strpos($rawResponse->signature, ":") === false) {
            return new APIResult(false, 'signature_header_invalid', $rawResponse);
        }
        list($keyId, $signature, $usedKey) = $this->parseSignatureHeader($rawResponse->signature);
        if ($keyId == null || $signature == null || $usedKey == null) {
            return new APIResult(false, 'signature_key_invalid', $rawResponse);
        }
        $computedSignature = hash_hmac('sha512', $signatureData, $usedKey);
        if ($computedSignature != $signature) {
            return new APIResult(false, 'signature_invalid', $rawResponse);
        }
        return null;
    }
    /**
     * @param APIRequest|APIResponse $rawResponse
     *
     * @return null|APIResult
     */
    private function verifyTimestamp($rawResponse)
    {
        if ($this->verifyTimestampMaxAgeMillis <= -1) {
            return null;
        }
        if ($rawResponse->jsonBody == null) {
            return new APIResult(false, 'body_not_json', $rawResponse);
        }
        $timestamp = $rawResponse->jsonBody->timestamp;
        if (!isset($timestamp) || $timestamp == null) {
            return new APIResult(false, 'body_no_timestamp', $rawResponse);
        }
        $now = time();
        // time() is UTC and server always returns UTC as well
        $timestampDifference = $now - $timestamp;
        if (abs($timestampDifference) > $this->verifyTimestampMaxAgeMillis) {
            return new APIResult(false, 'timestamp_out_of_range', $rawResponse);
        }
        return null;
    }
    private function assertApiKeyId()
    {
        if (!isset($this->apiKeyId)) {
            throw new \Exception('api key missing');
        }
        return $this->apiKeyId;
    }
    /**
     * @param string $apiKeyId
     *
     * @return string
     * @throws \Exception
     */
    private function assertApiKeySecret($apiKeyId)
    {
        $apiKeySecret = $this->apiKeys[$apiKeyId];
        if (!isset($apiKeySecret)) {
            throw new \Exception('api key secret missing');
        }
        return $apiKeySecret;
    }
    /**
     * @param integer $status
     *
     * @return string
     */
    private static function errorCodeForStatus($status)
    {
        switch ($status) {
            case 401:
                return 'unauthorized';
            case 403:
                return 'forbidden';
            case 404:
                return 'not_found';
        }
        if ($status / 100 == 5) {
            return 'server_error';
        }
        return "unknown_error";
    }
    /** @return string */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }
    /**
     * @param string $apiEndpoint
     * @return APIClient
     */
    public function setApiEndpoint($apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
        return $this;
    }
    /** @return string */
    public function getApiKeyId()
    {
        return $this->apiKeyId;
    }
    /**
     * @param string $apiKeyId
     * @return APIClient
     */
    public function setApiKeyId($apiKeyId)
    {
        $this->apiKeyId = $apiKeyId;
        return $this;
    }
    /** @return array */
    public function getApiKeys()
    {
        return $this->apiKeys;
    }
    /**
     * @param array $apiKeys
     * @return APIClient
     */
    public function setApiKeys($apiKeys)
    {
        $this->apiKeys = $apiKeys;
        return $this;
    }
    /**
     * @param boolean $verifySignature
     * @return APIClient
     */
    public function setVerifySignature($verifySignature)
    {
        $this->verifySignature = $verifySignature;
        return $this;
    }
    /**
     * @param int $verifyTimestampMaxAgeMillis
     * @return APIClient
     */
    public function setVerifyTimestampMaxAgeMillis($verifyTimestampMaxAgeMillis)
    {
        $this->verifyTimestampMaxAgeMillis = $verifyTimestampMaxAgeMillis;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt {
class APIRequest
{
    /** @var string protocol used to make the request */
    public $protocol;
    /** @var string the request method */
    public $method;
    /** @var string the requested path */
    public $path;
    /** @var string the IP address of the client executing the request */
    public $remoteAddress;
    /** @var array Associative array of HTTP headers (key = header name, value = header value). */
    public $headers;
    /** @var string HTTP body. */
    public $body;
    /** @var object HTTP body after json_decode. */
    public $jsonBody;
    /** @var string Value of the X-Pat-Signature HTTP header */
    public $signature;
    /**
     * APIResponse constructor.
     *
     * @param string $body
     * @param object $jsonBody
     * @param string $signature
     */
    public function __construct($body, $jsonBody, $signature)
    {
        $this->protocol = $_SERVER["SERVER_PROTOCOL"];
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->path = $_SERVER["REQUEST_URI"] . $_SERVER["QUERY_STRING"];
        // TODO checkme
        $this->remoteAddress = $_SERVER["REMOTE_ADDR"];
        $this->headers = APIRequest::getAllHeaders();
        $this->body = $body;
        $this->jsonBody = $jsonBody;
        $this->signature = $signature;
    }
    private static function getAllHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt {
class APIResponse
{
    /** @var int HTTP status of the response. */
    public $status;
    /** @var array Associative array of HTTP headers (key = header name, value = header value). */
    public $headers;
    /** @var string HTTP body. */
    public $body;
    /** @var object HTTP body after json_decode. */
    public $jsonBody;
    /** @var string Value of the X-Pat-Signature HTTP header. */
    public $signature;
    /**
     * APIResponse constructor.
     *
     * @param int    $status
     * @param array  $headers
     * @param string $body
     * @param object $jsonBody
     * @param string $signature
     */
    public function __construct($status, $headers, $body, $jsonBody, $signature)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
        $this->jsonBody = $jsonBody;
        $this->signature = $signature;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt {
class APIResult
{
    /** @var bool True of request was successful, false otherwise. */
    public $success;
    /** @var string HTTP error code if request wasn't successful. */
    public $errorCode;
    /** @var APIResponse Raw response as extracted from curl call. */
    public $rawResponse;
    /** @var mixed The parsed response if the request was successful. */
    public $result;
    /**
     * APIResult constructor.
     *
     * @param bool                   $success
     * @param string                 $errorCode
     * @param APIResponse|APIRequest $rawResponse
     */
    public function __construct($success, $errorCode, $rawResponse)
    {
        $this->success = $success;
        $this->errorCode = $errorCode;
        $this->rawResponse = $rawResponse;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
/**
 * Customer information.
 *
 * @package PurchasedAt\API
 */
class Customer
{
    /**
     * @see \PurchasedAt\Purchase\Customer::email
     * @var string Email address of the customer
     */
    private $email;
    /**
     * @see \PurchasedAt\Purchase\Customer::externalId
     * @var string External id of the customer provided by the vendor (i.e. customer id in your database).
     */
    private $externalId;
    /** @var string Customer country detected by purchased.at. */
    private $country;
    /** @var string Customer language detected by purchased.at */
    private $language;
    public static function fromJson($json)
    {
        $r = new Customer();
        $r->setEmail($json->email);
        $r->setExternalId(isset($json->external_id) ? $json->external_id : null);
        $r->setCountry($json->country);
        $r->setLanguage($json->language);
        return $r;
    }
    /** @return string */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @param string $email
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }
    /**
     * @param string $externalId
     * @return Customer
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
    /** @return string */
    public function getCountry()
    {
        return $this->country;
    }
    /**
     * @param string $country
     * @return Customer
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }
    /** @return string */
    public function getLanguage()
    {
        return $this->language;
    }
    /**
     * @param string $language
     * @return Customer
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
class CheckoutItem
{
    /** @var int Count of items sol.d */
    private $count;
    /** @var string Name of the item as displayed to the customer. */
    private $name;
    /** @var string User defined Stock Keeping Unit (SKU) of the item. */
    private $sku;
    /** @var string External identifier of the item. */
    private $externalId;
    /** @var \PurchasedAt\API\Price Price of a single item. */
    private $price;
    /** @var \PurchasedAt\API\Price Calculated total price of a items (count * price). */
    private $total;
    public static function fromJson($json)
    {
        $r = new CheckoutItem();
        $r->setCount($json->count);
        $r->setName(isset($json->name) ? $json->name : null);
        $r->setSku($json->sku);
        $r->setExternalId(isset($json->external_id) ? $json->external_id : null);
        $r->setPrice(Price::fromJson($json->price));
        $r->setTotal(Price::fromJson($json->total));
        return $r;
    }
    /** @return int */
    public function getCount()
    {
        return $this->count;
    }
    /**
     * @param int $count
     * @return CheckoutItem
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
    /** @return string */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param string $name
     * @return CheckoutItem
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    /** @return string */
    public function getSku()
    {
        return $this->sku;
    }
    /**
     * @param string $sku
     * @return CheckoutItem
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }
    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }
    /**
     * @param string $externalId
     * @return CheckoutItem
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
    /** @return Price */
    public function getPrice()
    {
        return $this->price;
    }
    /**
     * @param Price $price
     * @return CheckoutItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
    /** @return Price */
    public function getTotal()
    {
        return $this->total;
    }
    /**
     * @param Price $total
     * @return CheckoutItem
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
class Checkout
{
    /** @var \PurchasedAt\API\CheckoutItem[] Items sold in the checkout transaction. */
    private $items;
    /** @var \PurchasedAt\API\Price Total price of the checkout transaction */
    private $total;
    public static function fromJson($json)
    {
        $r = new Checkout();
        $r->setItems(array_map(function ($json) {
            return CheckoutItem::fromJson($json);
        }, $json->items));
        $r->setTotal(Price::fromJson($json->total));
        return $r;
    }
    /**@return \PurchasedAt\API\CheckoutItem[] */
    public function getItems()
    {
        return $this->items;
    }
    /**
     * @param \PurchasedAt\API\CheckoutItem[] $items
     * @return Checkout
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }
    /** @return \PurchasedAt\API\Price */
    public function getTotal()
    {
        return $this->total;
    }
    /**
     * @param \PurchasedAt\API\Price $total
     * @return Checkout
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
class Item
{
    /** @var string Server defined id of the item. (UUID) */
    private $id;
    /** @var string User defined Stock Keeping Unit (SKU) of the item. */
    private $sku;
    public static function fromJson($json)
    {
        $r = new Item();
        $r->setId($json->id);
        $r->setSku($json->sku);
        return $r;
    }
    /**@return string */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string $id
     * @return Item
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /** @return string */
    public function getSku()
    {
        return $this->sku;
    }
    /**
     * @param string $sku
     * @return Item
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
/**
 * Price information.
 *
 * @package PurchasedAt\API
 */
class Price
{
    /** @var number Gross price (parsed by json as string to preserver accuracy). */
    private $gross;
    /** @var string ISO4271 3 letter currency code. */
    private $currency;
    public static function fromJson($json)
    {
        $r = new Price();
        $r->setGross($json->gross);
        $r->setCurrency($json->currency);
        return $r;
    }
    /** @return number */
    public function getGross()
    {
        return $this->gross;
    }
    /**
     * @param number $gross
     * @return Price
     */
    public function setGross($gross)
    {
        $this->gross = $gross;
        return $this;
    }
    /** @return string */
    public function getCurrency()
    {
        return $this->currency;
    }
    /**
     * @param string $currency
     * @return Price
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
/**
 * Class RedirectData
 *
 * @package PurchasedAt\API
 */
class RedirectData
{
    /** @var string server defined id of the transaction. */
    private $transactionId;
    /**
     * @see \PurchasedAt\Purchase\Transaction::externalId
     * @var string external id of the transaction as provided by the purchase script.
     */
    private $externalTransactionId;
    public static function fromRequest()
    {
        $r = new RedirectData();
        if (isset($_GET["pat-tx"])) {
            $r->transactionId = $_GET["pat-tx"];
        }
        if (isset($_GET["pat-etx"])) {
            $r->externalTransactionId = $_GET["pat-tx"];
        }
        return $r;
    }
    /** @return mixed */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
    /** @return mixed */
    public function getExternalTransactionId()
    {
        return $this->externalTransactionId;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
/**
 * Transaction information.
 *
 * @package PurchasedAt\API
 */
class Transaction
{
    /** @var string Server defined id of the transaction. (UUID) */
    private $id;
    /**
     * @see \PurchasedAt\Purchase\Transaction::externalId
     * @var string External id of the transaction as provided by the purchase script.
     */
    private $externalId;
    /** @var string Creation date of the transaction as unix timestamp (seconds, UTC) */
    private $created;
    /**
     * @link https://docs.purchased.at/display/PUR/API#API-transaction-state
     * @var string State of the transaction.
     */
    private $state;
    /** @var int Revision number of the transaction, i.e. how many state changes the transaction has gone through. */
    private $revisionNumber;
    /** @var string Server defined id of the project. (UUID) */
    private $project;
    /** @var \PurchasedAt\API\Item Item handled in the transaction. (only one of item and checkout are non-null) */
    private $item;
    /** @var \PurchasedAt\API\Checkout Checkout handled in the transaction. (only one of item and checkout are non-null) */
    private $checkout;
    /** @var string Type of the transaction, one of "item", "checkout" */
    private $type;
    /** @var \PurchasedAt\API\Customer Customer information of the transaction. */
    private $customer;
    /** @var \PurchasedAt\API\Price Pricing information of the transaction */
    private $price;
    /** @var string The payment method used to purchase the item. */
    private $paymentMethod;
    /** @var boolean Defines if this transaction was done with test mode active (i.e. no actual funds were transferred). */
    private $test;
    public static function fromJson($json)
    {
        if (!isset($json->item) && !isset($json->checkout)) {
            throw new \Exception('both checkout and item are null');
        }
        if (isset($json->item) && isset($json->checkout)) {
            throw new \Exception('both checkout and item are non-null, only expected one of checkout or item');
        }
        $r = new Transaction();
        $r->setId($json->id);
        $r->setExternalId(isset($json->external_id) ? $json->external_id : null);
        $r->setCreated($json->created);
        $r->setState($json->state);
        $r->setRevisionNumber($json->revision_number);
        $r->setProject($json->project);
        $r->setItem(isset($json->item) ? Item::fromJson($json->item) : null);
        $r->setCheckout(isset($json->checkout) ? Checkout::fromJson($json->checkout) : null);
        $r->setCustomer(Customer::fromJson($json->customer));
        $r->setPrice(Price::fromJson($json->price));
        $r->setPaymentMethod($json->payment_method);
        $r->setTest(isset($json->test) ? $json->test : false);
        return $r;
    }
    /** @return string */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string $id
     * @return Transaction
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /** @return string */
    public function getExternalId()
    {
        return $this->externalId;
    }
    /**
     * @param string $externalId
     * @return Transaction
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
    /** @return string */
    public function getCreated()
    {
        return $this->created;
    }
    /**
     * @param string $created
     * @return Transaction
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }
    /** @return string */
    public function getState()
    {
        return $this->state;
    }
    /**
     * @param string $state
     * @return Transaction
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }
    /** @return int */
    public function getRevisionNumber()
    {
        return $this->revisionNumber;
    }
    /**
     * @param int $revisionNumber
     * @return Transaction
     */
    public function setRevisionNumber($revisionNumber)
    {
        $this->revisionNumber = $revisionNumber;
        return $this;
    }
    /** @return string */
    public function getProject()
    {
        return $this->project;
    }
    /**
     * @param string $project
     * @return Transaction
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }
    /** @return Item */
    public function getItem()
    {
        return $this->item;
    }
    /**
     * @param Item $item
     * @return Transaction
     */
    public function setItem($item)
    {
        if (!is_null($item)) {
            $this->checkout = null;
            $this->item = $item;
            $this->type = 'item';
        } else {
            $this->item = null;
        }
        return $this;
    }
    /** @return Checkout */
    public function getCheckout()
    {
        return $this->checkout;
    }
    /**
     * @param Checkout $checkout
     * @return Transaction
     */
    public function setCheckout($checkout)
    {
        if (!is_null($checkout)) {
            $this->item = null;
            $this->checkout = $checkout;
            $this->type = 'checkout';
        } else {
            $this->checkout = null;
        }
        return $this;
    }
    /**
     * no setter, is automatically set with setCheckout and setItem
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /** @return Customer */
    public function getCustomer()
    {
        return $this->customer;
    }
    /**
     * @param Customer $customer
     * @return Transaction
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }
    /** @return Price */
    public function getPrice()
    {
        return $this->price;
    }
    /**
     * @param Price $price
     * @return Transaction
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
    /** @return string */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
    /**
     * @param string $paymentMethod
     * @return Transaction
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }
    /** @return boolean */
    public function isTest()
    {
        return $this->test;
    }
    /**
     * @param boolean $test
     * @return Transaction
     */
    public function setTest($test)
    {
        $this->test = $test;
        return $this;
    }
}
}

/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace PurchasedAt\API {
/**
 * Data sent by a notification API call.
 *
 * @package PurchasedAt\API
 */
class TransactionNotification
{
    /** @var string Server defined id of the transaction. (UUID) */
    private $transactionId;
    /**
     * @see \PurchasedAt\Purchase\Transaction::externalId
     * @var string External id of the transaction provided by the vendor (i.e. you)
     */
    private $externalTransactionId;
    /**
     * @link https://docs.purchased.at/display/PUR/API#API-transaction-state
     * @var string New state of the transaction. (new because the notification was triggered because of this state
     *      change)
     */
    private $newState;
    /** @var int Revision number of the transaction, i.e. how many state changes the transaction has gone through. */
    private $revisionNumber;
    /** @var integer */
    private $timestamp;
    /** @var boolean Defines if this transaction was done with test mode active (i.e. no actual funds were transferred). */
    private $test;
    public static function fromJson($json)
    {
        $r = new TransactionNotification();
        $r->transactionId = $json->transaction;
        $r->externalTransactionId = isset($json->external_transaction) ? $json->external_transaction : null;
        $r->newState = $json->new_state;
        $r->revisionNumber = $json->revision_number;
        $r->timestamp = $json->timestamp;
        $r->test = isset($json->test) ? $json->test : false;
        return $r;
    }
    /**
     * @deprecated use getTransactionId
     * @return string
     */
    public function getTransaction()
    {
        return $this->transactionId;
    }
    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
    /**
     * @param string $transactionId
     * @return TransactionNotification
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }
    /** @return string */
    public function getExternalTransactionId()
    {
        return $this->externalTransactionId;
    }
    /**
     * @param string $externalTransactionId
     * @return TransactionNotification
     */
    public function setExternalTransactionId($externalTransactionId)
    {
        $this->externalTransactionId = $externalTransactionId;
        return $this;
    }
    /** @return string */
    public function getNewState()
    {
        return $this->newState;
    }
    /**
     * @param string $newState
     * @return TransactionNotification
     */
    public function setNewState($newState)
    {
        $this->newState = $newState;
        return $this;
    }
    /** @return int */
    public function getRevisionNumber()
    {
        return $this->revisionNumber;
    }
    /**
     * @param int $revisionNumber
     * @return TransactionNotification
     */
    public function setRevisionNumber($revisionNumber)
    {
        $this->revisionNumber = $revisionNumber;
        return $this;
    }
    /** @return int */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * @param int $timestamp
     * @return TransactionNotification
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }
    /** @return boolean */
    public function isTest()
    {
        return $this->test;
    }
    /**
     * @param boolean $test
     * @return TransactionNotification
     */
    public function setTest($test)
    {
        $this->test = $test;
        return $this;
    }
}
}

namespace PurchasedAt {
/**
 * @param string                          $apiKey
 * @param \PurchasedAt\PurchaseOptions    $purchaseOptions the purchase options
 * @param string                          $target          id of target dom element
 * @param \PurchasedAt\Signing\JWTOptions $jwtOptions      the JWT options
 *
 * @return string HTML snippet to initialize the purchased.at widget
 * @throws \Exception when invalid data is set on the $purchaseOptions
 */
function renderScript($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
{
    return PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
}
}

namespace {
/**
 * @Deprecated
 */
function purchasedat_render_script($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
{
    return \PurchasedAt\PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
}
}

