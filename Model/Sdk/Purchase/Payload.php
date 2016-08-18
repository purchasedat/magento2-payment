<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\Purchase;

use PurchasedAt\Validation\Preconditions;

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

    /** @param string $customerEmail */
    public function __construct($customerEmail)
    {
        $this->customer = new Customer($customerEmail);
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

        return array(
            'customer'    => $this->customer->build(),
            'transaction' => $this->transaction->build(),
            'select'      => !is_null($this->select) ? $this->select->build() : null,
            'checkout'    => !is_null($this->checkout) ? $this->checkout->build() : null,
            'response'    => $this->response->build(),
            'test'        => $this->test->build(),
            'sdk'         => $this->sdk,
        );
    }

    /** @return Customer */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
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
     * @return $this
     */
    public function setTest(Test $test)
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
     * @return $this
     */
    public function setTransaction(Transaction $transaction = NULL)
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
     * @return $this
     */
    public function setSelect(Select $select = NULL)
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
     * @return $this
     */
    public function setCheckout(Checkout $checkout = NULL)
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
     * @return $this
     */
    public function setResponse(Response $response = NULL)
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
     * @return $this
     */
    public function setSdk($sdk)
    {
        Preconditions::checkStringNonEmpty($sdk, 'sdk');

        $this->sdk = $sdk;
        return $this;
    }

}
