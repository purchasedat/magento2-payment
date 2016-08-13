<?php
/**
 * This code is part of the purchased.at client SDK.
 *
 * @see https://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk;

use Magento\PurchasedAt\Model\Sdk\Purchase\Checkout;
use Magento\PurchasedAt\Model\Sdk\Purchase\Test;

/**
 * This class allows for setting various purchase options. The minimum you need to provide is the customer's e-mail
 * address.
 */
class PurchaseOptions
{

    const REVISION_LATEST    = Test::REVISION_LATEST;
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
        $this->payload = new Purchase\Payload($customerEmail);
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
        if($this->payload->getSelect() !== null && $this->payload->getSelect()->getItem() !== null) {
            throw new \LogicException('cannot activate checkout and $select->$item at the same time.');
        }

        $this->payload->setSelect(null);
        $checkout = new Checkout();
        $this->payload->setCheckout($checkout);
        return $checkout;
    }

}
