<?php
namespace Antavo\LoyaltyApps\Helper\App\FriendReferral;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\App\FriendReferral as FriendReferralHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;
use Magento\Framework\View\LayoutInterface as LayoutHelper;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order;

/**
 *
 */
class CheckoutObserver implements ObserverInterface
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\App\FriendReferral
     */
    private $_friendReferralHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_orderModel;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $_cookieManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $_layoutHelper;

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
     * @return \Magento\Sales\Model\Order
     */
    private function getLastOrder()
    {
        if (!$orderId = $this->_checkoutSession->getLastRealOrder()->getId()) {
            return NULL;
        }

        $order = $this->_orderModel->load($orderId);

        if (!$order->getRealOrderId()) {
            return NULL;
        }

        return $order;
    }

    /**
     * @param \Antavo\LoyaltyApps\Helper\App\FriendReferral $friendReferralHelper
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\View\LayoutInterface $layoutHelper
     */
    public function __construct(
        FriendReferralHelper $friendReferralHelper,
        ApiClient $apiClient,
        CheckoutSession $checkoutSession,
        OrderModel $orderModel,
        CookieManager $cookieManager,
        LayoutHelper $layoutHelper
    ) {
        $this->_friendReferralHelper = $friendReferralHelper;
        $this->_apiClient = $apiClient;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderModel = $orderModel;
        $this->_cookieManager = $cookieManager;
        $this->_layoutHelper = $layoutHelper;
    }

    /**
     * @return string
     */
    private function getReferralPopup()
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->_layoutHelper->createBlock('Antavo\LoyaltyApps\Block\Frontend\FriendReferral\Popup');
        $block->setTemplate('Antavo_LoyaltyApps::friendreferral/popup.phtml');
        return $block->toHtml();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function sendCouponRedeemEvent(Order $order)
    {
        try {
            $this->_apiClient->sendEvent(
                $order->getCustomerId(),
                'coupon_redeem',
                [
                    'code' => $order->getCouponCode(),
                    'amount' => -1 * $order->getBaseDiscountAmount(),
                    'transaction_id' => $order->getRealOrderId(),
                ]
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
        if (!$this->_friendReferralHelper->isEnabled()) {
            return;
        }

        if ($order = $this->getLastOrder()) {
            if ($this->getReferralCookie()) {
                /** @var \Magento\Framework\App\Response\Http $response */
                $response = $observer->getEvent()->getData('response');

                if (!$response) {
                    return;
                }

                $response->setBody($this->getReferralPopup());
                $this->removeReferralCookie();
            }

            if ($this->_friendReferralHelper->isFriendReferralCouponCode($order->getCouponCode())) {
                $this->sendCouponRedeemEvent($order);
            }
        }
    }
}
