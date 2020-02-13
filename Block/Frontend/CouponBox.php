<?php
namespace Antavo\LoyaltyApps\Block\Frontend;

use Antavo\LoyaltyApps\Helper\Cart as CartHelper;
use Antavo\LoyaltyApps\Helper\Customer as CustomerHelper;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 *
 */
class CouponBox extends Template
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Customer
     */
    private $_customerHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout
     */
    private $_checkoutHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cart
     */
    private $_cartHelper;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue(
            AntavoConfigInterface::XML_PATH_COUPON_SETTINGS_BOX,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return int
     */
    public function getPointsBurned()
    {
        return $this->_cartHelper->getPointsBurned();
    }

    /**
     * @return int
     */
    public function getPointRedeemLimit()
    {
        return $this->_cartHelper->getPointRedeemLimit();
    }

    /**
     * @return string
     */
    public function getCurrentStoreCurrencySymbol()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore();
        return $store->getCurrentCurrency()->getCurrencySymbol();
    }

    /**
     * @return int
     */
    public function getCustomerSpendablePoints()
    {
        return max(0, $this->_customerHelper->getSpendablePoints());
    }

    /**
     * @param int $points
     * @return float
     */
    public function calculateCouponValue($points)
    {
        return $this->_cartHelper->calculateCouponValue($points);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getStoreUrl($route, $params = []) {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore();
        return $store->getUrl($route, $params);
    }

    /**
     * @param Template\Context $context
     * @param \Antavo\LoyaltyApps\Helper\Customer $customerHelper
     * @param \Antavo\LoyaltyApps\Helper\Checkout $checkoutHelper
     * @param \Antavo\LoyaltyApps\Helper\Cart $cartHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerHelper $customerHelper,
        CheckoutHelper $checkoutHelper,
        CartHelper $cartHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerHelper = $customerHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_cartHelper = $cartHelper;
    }
}
