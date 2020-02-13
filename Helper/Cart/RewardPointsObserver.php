<?php
namespace Antavo\LoyaltyApps\Helper\Cart;

use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Antavo\LoyaltyApps\Helper\Customer as CustomerHelper;
use Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * This class handles two main Magento events:
 *  - controller_action_predispatch_checkout_index_index
 *  - controller_action_predispatch_checkout_cart_index
 */
class RewardPointsObserver implements ObserverInterface
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Customer
     */
    private $_customerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Antavo\LoyaltyApps\Helper\Customer $customerHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        CustomerHelper $customerHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_customerHelper = $customerHelper;
    }

    /**
     * This method determines how many points can be exchanged
     * for a single purchase.
     *
     * @return int
     */
    private function getMaximumRedeemablePoints()
    {
        /** @var \Magento\Reward\Helper\Data $dataHelper */
        $dataHelper = $this->getObjectManager()->get('\Magento\Reward\Helper\Data');
        /** @var \Magento\Reward\Model\Reward $rewardModel */
        $rewardModel = $this->getObjectManager()->get('\Magento\Reward\Model\Reward');
        return $dataHelper->getGeneralConfig(
            'max_points_balance',
            $rewardModel->getWebsiteId()
        );
    }

    /**
     * @return \Magento\Framework\App\ObjectManager
     */
    private function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Returns the customer spendable points by Auth endpoint.
     *
     * @return int
     */
    private function getCustomerPoints()
    {
        return (int) max(
            $this->_customerHelper->getSpendablePointsFromRequest(),
            0
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        // If the plugin is not enabled yet, return
        if (!$this->_scopeConfig->getValue(AntavoConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
            return TRUE;
        }

        $pointSpendingType = $this->_scopeConfig->getValue(
            AntavoConfigInterface::XML_PATH_POINT_MECHANISM
        );

        // If the plugin generates coupons from points automatically, we should not
        // update the customer's redeemable points
        if ($pointSpendingType != PointMechanismType::USING_REWARDS) {
            return TRUE;
        }

        /** @var \Magento\Reward\Model\Reward $rewardModel */
        $rewardModel = $this->getObjectManager()->create('\Magento\Reward\Model\Reward');

        // Calculating spendable points via Auth endpoint
        $loyaltyPoints = $this->getCustomerPoints();

        // Getting the customer's id from the session
        $customerId = $this->_customerSession->getCustomerId();

        $rewardModelData = $rewardModel->load(
            $customerId,
            'customer_id'
        );

        // Getting the maximum of redeemable points; this one defines
        // how many points can be exchanged for a single purchase
        $pointLimit = $this->getMaximumRedeemablePoints();

        if ($pointLimit && $loyaltyPoints > $pointLimit) {
            $loyaltyPoints = $pointLimit;
        }

        // update if loyalty points and reward point balance don't match
        if ($rewardModelData->getPointsBalance() != $loyaltyPoints) {
            try {
                /** @var \Magento\Reward\Model\Reward */
                $this
                    ->getObjectManager()
                    ->create('Magento\Reward\Model\Reward')
                    ->setCustomerId($customerId)
                    ->loadByCustomer()
                    ->setPointsBalance($loyaltyPoints)
                    ->setComment('Antavo Loyalty Points: ' . $loyaltyPoints)
                    ->save();
            } catch (\Exception $e) {
                // Failing silently...
            }
        }

        return TRUE;
    }
}
