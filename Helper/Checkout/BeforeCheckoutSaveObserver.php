<?php
namespace Antavo\LoyaltyApps\Helper\Checkout;

use Antavo\LoyaltyApps\Helper\Cart as CartHelper;
use Antavo\LoyaltyApps\Helper\Customer as CustomerHelper;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\SourceModels\CheckoutSendingType;
use Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\State;

/**
 *
 */
class BeforeCheckoutSaveObserver implements ObserverInterface
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout
     */
    private $_checkoutHelper;

    /**
     * @var \Magento\Framework\App\State
     */
    private $_state;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Customer
     */
    private $_customerHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cart
     */
    private $_cartHelper;

    /**
     * @param \Antavo\LoyaltyApps\Helper\Checkout $checkoutHelper
     * @param \Magento\Framework\App\State $state
     * @param \Antavo\LoyaltyApps\Helper\Customer $customerHelper
     * @param \Antavo\LoyaltyApps\Helper\Cart $cartHelper
     */
    public function __construct(
        CheckoutHelper $checkoutHelper,
        State $state,
        CustomerHelper $customerHelper,
        CartHelper $cartHelper
    ) {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_state = $state;
        $this->_customerHelper = $customerHelper;
        $this->_cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->_checkoutHelper->getPointMechanismType() != PointMechanismType::USING_REWARDS) {
                return;
            }

            $orderData = $observer->getData('order');

            if ($this->_checkoutHelper->getCheckoutSendingType($orderData) != CheckoutSendingType::TYPE_PURCHASE_COMPLETED) {
                return;
            }

            if (!isset($orderData['reward_points_balance'])) {
                return;
            }

            $pointsBurned = $orderData['reward_points_balance'];

            // admin and frontend have to be separated
            if ($this->_state->getAreaCode() != 'adminhtml') {
                $this->_cartHelper->setPointsBurned($pointsBurned);

                // more points burned than customer has in Antavo
                if ($pointsBurned > $this->_customerHelper->getSpendablePointsFromRequest()) {
                    return;
                }
            }
        } catch (\Exception $e) {
            // Failing silently...
        }
    }
}
