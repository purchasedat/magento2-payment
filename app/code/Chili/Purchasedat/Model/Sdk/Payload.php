<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
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