<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
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
