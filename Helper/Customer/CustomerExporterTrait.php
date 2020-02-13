<?php
namespace Antavo\LoyaltyApps\Helper\Customer;

/**
 *
 */
trait CustomerExporterTrait
{
    /**
     * @param mixed $customer
     * @return array
     */
    public function exportCustomerProperties($customer)
    {
        return [
            'handler' => $customer->getFirstname(),
            'email' => $customer->getEmail(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
        ];
    }
}
