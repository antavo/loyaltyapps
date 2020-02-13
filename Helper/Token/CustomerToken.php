<?php
namespace Antavo\LoyaltyApps\Helper\Token;

use Antavo\LoyaltyApps\Helper\Token;

/**
 *
 */
class CustomerToken extends Token
{
    /**
     * {@inheritdoc}
     */
    public $algorithm = 'sha256';

    /**
     * {@inheritdoc}
     */
    public $expires_at = 86400;

    /**
     * Returns unique customer ID from payload.
     *
     * @return mixed  Returns customer ID if set, <tt>NULL</tt> otherwise.
     */
    public function getCustomer()
    {
        if (isset($this->data['customer'])) {
            return $this->data['customer'];
        }

        return NULL;
    }

    /**
     * Sets unique customer ID for payload.
     *
     * @param mixed $customer  Unique customer ID. Non-scalar values are
     * discarded.
     * @return self  Object instance for method chaining.
     */
    public function setCustomer($customer)
    {
        if (is_scalar($customer)) {
            $this->data['customer'] = $customer;
        }

        return $this;
    }
}
