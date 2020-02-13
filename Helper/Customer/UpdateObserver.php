<?php
namespace Antavo\LoyaltyApps\Helper\Customer;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * This class handles two main Magento events:
 *  - customer_save_before
 *  - customer_save_after
 */
class UpdateObserver implements ObserverInterface
{
    use CustomerExporterTrait;

    /**
     * @var bool
     */
    private $_skip = FALSE;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @return bool
     */
    private function isSkip()
    {
        return $this->_skip;
    }

    /**
     * @param bool $skip
     * @return $this
     */
    private function setSkip($skip)
    {
        $this->_skip = $skip;
        return $this;
    }

    /**
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->_apiClient = $apiClient;
    }

    /**
     * This method avoids multiple profile event sending.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function handleCustomerSaveBefore(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getData('customer');

        if (!$customer->getId()) {
            $this->setSkip(TRUE);
        }
    }

    /**
     * This method send the exported customer attributes to the Events API.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function handleCustomerSaveAfter(Observer $observer)
    {
        try {
            if ($this->isSkip()) {
                return;
            }

            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $observer->getData('customer');

            $this
                ->_apiClient
                ->sendEvent(
                    $customer->getId(),
                    'profile',
                    $this->exportCustomerProperties(
                        $customer->getDataModel()
                    )
                );
        } catch (\Exception $e) {
            // Failing silently...
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();

        if ('customer_save_before' == $eventName) {
            $this->handleCustomerSaveBefore($observer);
        } elseif ('customer_save_after' == $eventName) {
            $this->handleCustomerSaveAfter($observer);
        }

        return TRUE;
    }
}
