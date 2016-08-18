<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\Purchase;

use PurchasedAt\Validation\Preconditions;

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
        return array(
            'external_id' => $this->externalId,
        );
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
        if (!is_null($externalId)) {
            Preconditions::checkStringNonEmpty($externalId, 'externalId');
        }

        $this->externalId = $externalId;
        return $this;
    }

}
