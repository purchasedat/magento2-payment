<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
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
