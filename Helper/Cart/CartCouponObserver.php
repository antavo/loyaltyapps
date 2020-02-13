<?php
namespace Antavo\LoyaltyApps\Helper\Cart;

use Antavo\LoyaltyApps\Helper\Cart as CartHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class CartCouponObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cart
     */
    private $_cartHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Antavo\LoyaltyApps\Helper\Cart $cartHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CartHelper $cartHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_cartHelper = $cartHelper;
    }

    /**
     * @return bool
     */
    public function isCouponAutoGenerationEnabled()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_AUTO_GENERATE_COUPON,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return bool
     */
    public function isCouponGenerationEnabled()
    {
        return PointMechanismType::USING_COUPONS == $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_POINT_MECHANISM
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        // If the plugin is not enabled yet, return
        if (!$this->_scopeConfig->getValue(ConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
            return TRUE;
        }

        // If the loyalty coupon generation is disabled, return
        if (!$this->isCouponGenerationEnabled()) {
            return TRUE;
        }

        // If the coupon auto-generation is not enabled, return
        if (!$this->isCouponAutoGenerationEnabled()) {
            return TRUE;
        }

        $this->_cartHelper->handleCartCoupon(
            $observer->getData('cart'),
            $this->_cartHelper->getPointsBurned()
        );
        return TRUE;
    }
}
