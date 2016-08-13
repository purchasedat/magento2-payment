<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk\Purchase;

use Magento\PurchasedAt\Model\Sdk\Validation\Preconditions;
use Magento\PurchasedAt\Model\Sdk\Validation\Verify;

class Customer
{
    /** @var string */
    private $email;
    /** @var string */
    private $externalId;

    /** @param string $email */
    public function __construct($email)
    {
        Preconditions::checkStringNonEmpty($email, 'email');

        $this->email = $email;
    }


    public function build()
    {
        Verify::verifyStringNonEmpty($this->email, 'email');

        return array(
            'email'       => $this->email,
            'external_id' => $this->externalId,
        );
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
        Preconditions::checkStringNonEmpty($email, 'email');

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
        if (!is_null($externalId)) {
            Preconditions::checkStringNonEmpty($externalId, 'externalId');
        }

        $this->externalId = $externalId;
        return $this;
    }

}
