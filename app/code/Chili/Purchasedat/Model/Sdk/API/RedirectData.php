<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk\API {
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