<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\API;

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
