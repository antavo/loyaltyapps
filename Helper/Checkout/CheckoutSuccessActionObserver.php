<?php
namespace Antavo\LoyaltyApps\Helper\Checkout;

use Antavo\LoyaltyApps\Helper\Cart as CartHelper;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Antavo\LoyaltyApps\Helper\SourceModels\CheckoutSendingType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 *
 */
class CheckoutSuccessActionObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_orderModel;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout
     */
    private $_checkoutHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout\ReserveHelper
     */
    private $_reserveHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cart
     */
    private $_cartHelper;

    /**
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Antavo\LoyaltyApps\Helper\Checkout $checkoutHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Antavo\LoyaltyApps\Helper\Checkout\ReserveHelper $reserveHelper
     * @param \Antavo\LoyaltyApps\Helper\Cart $cartHelper
     */
    public function __construct(
        OrderModel $orderModel,
        CheckoutSession $checkoutSession,
        CheckoutHelper $checkoutHelper,
        ScopeConfigInterface $scopeConfig,
        ReserveHelper $reserveHelper,
        CartHelper $cartHelper
    ) {
        $this->_orderModel = $orderModel;
        $this->_checkoutSession = $checkoutSession;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_reserveHelper = $reserveHelper;
        $this->_cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        // If there is no checkout in the session, return
        if (!$order = $this->_checkoutHelper->getLastOrder()) {
            return TRUE;
        }

        // If the checkout sending mechanism is not enabled yet, return
        if (!$this->_checkoutHelper->isCheckoutEventSendingEnabled($order->getStore()->getId())) {
            return TRUE;
        }

        // If the checkout is immediately will be sent to the Events API,
        // there is no need for reserving the burned points
        if ($this->_checkoutHelper->getCheckoutSendingType($order) != CheckoutSendingType::TYPE_PURCHASE_COMPLETED) {
            return TRUE;
        }

        try {
            // Reserving the burned points; this one make sure that customer can't burn
            // its points multiple times
            $this->_reserveHelper->reserve(
                $order,
                (int) $this->_cartHelper->getPointsBurned()
            );
        } catch (\Exception $e) {
            // Do nothing...
        }

        return TRUE;
    }
}
