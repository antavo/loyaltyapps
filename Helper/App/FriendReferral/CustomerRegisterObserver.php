<?php
namespace Antavo\LoyaltyApps\Helper\App\FriendReferral;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\App\Coupons\FriendReferral as FriendReferralHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;

/**
 *
 */
class CustomerRegisterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $_cookieManager;

    /**
     * @var FriendReferralHelper
     */
    private $_friendReferralHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param FriendReferralHelper $friendReferralHelper
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     */
    public function __construct(
        CookieManager $cookieManager,
        FriendReferralHelper $friendReferralHelper,
        ApiClient $apiClient
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_friendReferralHelper = $friendReferralHelper;
        $this->_apiClient = $apiClient;
    }

    /**
     * Returns referrer customer ID (if there is one).
     *
     * @return string
     */
    private function getReferralCookie()
    {
        return $this->_cookieManager->getCookie(
            FriendReferralHelper::COOKIE_NAME
        );
    }

    /**
     * This method removes the friend referral cookie.
     */
    private function removeReferralCookie()
    {
        try {
            $this->_cookieManager->deleteCookie(
                FriendReferralHelper::COOKIE_NAME
            );
        } catch (\Exception $e) {
            // Failing silently...
        }
    }

    /**
     * @param string $customerId
     * @param string $referredId
     */
    private function sendReferralEvent($customerId, $referredId)
    {
        try {
            $this->_apiClient->sendEvent(
                $customerId,
                'referral',
                ['referred' => $referredId]
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
        // If the Friend Referral app is not enabled yet, return
        if (!$this->_friendReferralHelper->isEnabled()) {
            return;
        }

        if ($referrerId = $this->getReferralCookie()) {
            /** @var \Magento\Customer\Model\Customer $referred */
            $referred = $observer->getData('customer');

            // Sending in the referral event
            $this->sendReferralEvent($referrerId, $referred->getId());

            // Removing the referral cookie to avoid multiple referral events
            $this->removeReferralCookie();
        }
    }
}
