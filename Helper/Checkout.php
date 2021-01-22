<?php
namespace Antavo\LoyaltyApps\Helper;

use Antavo\LoyaltyApps\Helper\Coupon as CouponHelper;
use Antavo\LoyaltyApps\Helper\Cart as CartHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Invoice;
use Magento\SalesRule\Model\Coupon as CouponModel;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class Checkout
{
    /**
     * @var string
     */
    const CHANNEL_COOKIE_NAME = '__ats';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_orderModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    private $_ruleModel;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $_categoryModel;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    private $_couponModel;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cart
     */
    private $_cartHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Coupon
     */
    private $_couponHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $_cookieManager;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_productModel;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param \Magento\SalesRule\Model\Coupon $couponModel
     * @param \Antavo\LoyaltyApps\Helper\Cart $cartHelper
     * @param \Antavo\LoyaltyApps\Helper\Coupon $couponHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Catalog\Model\Product $productModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        OrderModel $orderModel,
        StoreManagerInterface $storeManager,
        RuleModel $ruleModel,
        CategoryModel $categoryModel,
        CouponModel $couponModel,
        CartHelper $cartHelper,
        CouponHelper $couponHelper,
        CookieManagerInterface $cookieManager,
        ProductModel $productModel
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderModel = $orderModel;
        $this->_storeManager = $storeManager;
        $this->_ruleModel = $ruleModel;
        $this->_categoryModel = $categoryModel;
        $this->_couponModel = $couponModel;
        $this->_cartHelper = $cartHelper;
        $this->_couponHelper = $couponHelper;
        $this->_cookieManager = $cookieManager;
        $this->_productModel = $productModel;
    }

    /**
     * @param string $storeId
     * @return bool
     */
    public function isCheckoutEventSendingEnabled($storeId)
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_CHECKOUT_EVENT_SENDING,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getChannelCookie()
    {
        return $this->_cookieManager->getCookie(self::CHANNEL_COOKIE_NAME);
    }

    /**
     * @param int $points
     * @return int
     */
    public function calculateCouponValue($points)
    {
        return $points * $this->getPointRate();
    }

    /**
     * @return mixed
     */
    public function getPointRate()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_POINT_RATE,
            ScopeInterface::SCOPE_STORES,
            NULL
        );
    }

    /**
     * @param float $couponValue
     * @return int
     */
    public function calculatePointsBurned($couponValue)
    {
        return (int) floor(
            $couponValue / $this->_scopeConfig->getValue(
                ConfigInterface::XML_PATH_POINT_RATE,
                ScopeInterface::SCOPE_STORES,
                NULL
            )
        );
    }

    /**
     * Checks if code was issued with a point burning coupon.
     *
     * @param string $code
     * @return bool
     */
    private function isPointBurningCouponCode($code)
    {
        return preg_match('/^' . $this->_couponHelper->getCheckoutCouponCodePrefix() . '-\d+-\d{6}-[A-Z\d]+$/', $code);
    }

    /**
     * Invokes {self::calculateGenericPoints()}, if it is necessary.
     *
     * @param array $data
     * @return array
     */
    public function applyGenericPoints(array $data)
    {
        if (!isset($data['rewarded_points'])) {
            return $data;
        }

        $data['rewarded_points'] = $this->calculateCouponValue(
            $data['total'] - $this->calculatePointsBurned($data['rewarded_points'])
        );
        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductCategories(ProductModel $product)
    {
        return array_filter(
            array_reduce(
                $product->getCategoryIds(),
                function (array $categoryContainer, $category) {
                    $categoryContainer[] = $this->_categoryModel
                        ->load($category)
                        ->getName();
                    return $categoryContainer;
                },
                []
            )
        );
    }

    /**
     * @return string
     */
    public function getPointMechanismType()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_POINT_MECHANISM
        );
    }

    /**
     * @return bool
     */
    public function isBaseCurrencyConvertEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_CONVERT_CURRENCY,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getOrderCurrency(OrderModel $order)
    {
        return $this->isBaseCurrencyConvertEnabled()
            ? $order->getBaseCurrencyCode()
            : $order->getOrderCurrencyCode();
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return float
     */
    public function getInvoiceTotal(Invoice $invoice)
    {
        return $this->isBaseCurrencyConvertEnabled()
            ? $invoice->getBaseGrandTotal()
            : $invoice->getGrandTotal();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getOrderTotal(OrderModel $order) {
        return $this->isBaseCurrencyConvertEnabled()
            ? $order->getBaseGrandTotal()
            : $order->getGrandTotal();
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item
     * @return float
     */
    public function getProductTotalInvoiceItem(InvoiceItem $item)
    {
        return $this->isBaseCurrencyConvertEnabled()
            ? $item->getBasePriceInclTax()
            : $item->getPriceInclTax();
    }

    /**
     * @param OrderModel\Item $item
     * @return float
     */
    public function getProductTotalOrderItem(OrderModel\Item $item)
    {
        return $this->isBaseCurrencyConvertEnabled()
            ? $item->getBasePriceInclTax()
            : $item->getPriceInclTax();
    }

    /**
     * @param string $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function fetchProduct($productId)
    {
        $product = $this->_productModel->load($productId);

        if (!$product->getId()) {
            return NULL;
        }

        return $product;
    }

    /**
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @return \Magento\SalesRule\Model\Rule
     */
    public function fetchCouponRule(CouponModel $coupon)
    {
        $rule = $this->_ruleModel->load($coupon->getRuleId());

        if (!$rule->getId()) {
            return NULL;
        }

        return $rule;
    }

    /**
     * @param string $couponCode
     * @return \Magento\SalesRule\Model\Rule
     */
    public function getCouponRule($couponCode)
    {
        $coupon = $this->_couponModel->load($couponCode, 'code');

        if (!$coupon->getId() || !$coupon->getRuleId() || !$this->isPointBurningCouponCode($coupon->getCode())) {
            return NULL;
        }

        return $this->fetchCouponRule($coupon);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getLastOrder()
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
     * Helper to get payment type name
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getCheckoutSendingType(OrderModel $order) {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_CHECKOUT_SENDING_TYPE,
            ScopeInterface::SCOPE_STORES,
            $order->getStore()->getId()
        );
    }
}
