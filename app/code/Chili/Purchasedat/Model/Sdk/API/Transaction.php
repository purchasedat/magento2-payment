<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk\API {
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