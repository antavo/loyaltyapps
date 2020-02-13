<?php
namespace Antavo\LoyaltyApps\Helper;

use Antavo\LoyaltyApps\Helper\Coupon as CouponHelper;
use Antavo\LoyaltyApps\Helper\Customer as CustomerHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Customer\Model\Group as CustomerGroupModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeHelper;
use Magento\SalesRule\Model\Coupon as CouponModel;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\RuleFactory;

/**
 *
 */
class Cart
{
    /**
     * Number of points to be burned with checkout is set under this session key.
     * Upon finding it unset, the max available points will be calculated.
     * If set to 0, no points will be burned with current checkout.
     *
     * @var string
     */
    const POINTS_BURNED_SESSION_KEY = 'AntavoCouponPointsBurned';

    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $_catalogSession;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Customer
     */
    private $_customerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    private $_couponModel;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    private $_ruleModel;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Coupon
     */
    private $_couponHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_dateTimeHelper;

    /**
     * @var \Magento\Customer\Model\Group
     */
    private $_customerGroupModel;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $_ruleFactory;

    /**
     * Returns number of points to be burned from session.
     *
     * @return mixed
     */
    public function getPointsBurned()
    {
        return $this->_catalogSession->{'get' . self::POINTS_BURNED_SESSION_KEY}();
    }

    /**
     * Stores number of points to be burned in session.
     *
     * @param mixed $points
     * @return $this
     */
    public function setPointsBurned($points)
    {
        $this->_catalogSession->{'set' . self::POINTS_BURNED_SESSION_KEY}($points);
        return $this;
    }

    /**
     * @return int
     */
    public function getPointRedeemLimit()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_POINT_BURNING_LIMIT,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        ) ?: 0;
    }

    /**
     * @return float
     */
    public function getPointRate()
    {
        return (float) $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_POINT_RATE,
            ScopeInterface::SCOPE_STORES,
            NULL
        );
    }

    /**
     * @param int $points
     * @return float
     */
    public function calculateCouponValue($points)
    {
        return $points * $this->getPointRate();
    }

    /**
     * Calculates total amount of cart (including tax).
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @return float
     */
    protected function getCartTotal(CartModel $cart)
    {
        return array_reduce(
            $cart->getQuote()->getAllVisibleItems(),
            function ($total, Item $item) {
                return $total + $item->getPriceInclTax() * $item->getQty();
            },
            0
        );
    }

    /**
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Antavo\LoyaltyApps\Helper\Customer $customerHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Coupon $couponModel
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @param \Antavo\LoyaltyApps\Helper\Coupon $couponHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeHelper
     * @param \Magento\Customer\Model\Group $customerGroupModel
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        CatalogSession $catalogSession,
        CustomerHelper $customerHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CouponModel $couponModel,
        RuleModel $ruleModel,
        CouponHelper $couponHelper,
        DateTimeHelper $dateTimeHelper,
        CustomerGroupModel $customerGroupModel,
        RuleFactory $ruleFactory
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_customerHelper = $customerHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_couponModel = $couponModel;
        $this->_ruleModel = $ruleModel;
        $this->_couponHelper = $couponHelper;
        $this->_dateTimeHelper = $dateTimeHelper;
        $this->_customerGroupModel = $customerGroupModel;
        $this->_ruleFactory = $ruleFactory;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @return \Magento\SalesRule\Model\Rule
     */
    private function getCartCouponRule(CartModel $cart)
    {
        if (!$code = $cart->getQuote()->getCouponCode()) {
            return NULL;
        }

        $coupon = $this->_couponModel->load($code, 'code');

        if (!$coupon->getId()) {
            return NULL;
        }

        $rule = $this->_ruleModel->load($coupon->getRuleId());

        if (!$rule->getId()) {
            return NULL;
        }

        return $rule;
    }

    /**
     * Removes Antavo point burning coupon from cart, if there is one.
     *
     * @param CartModel $cart
     * @return bool  Returns TRUE on successful delete; returns FALSE if no
     * code is present or coupon is not for point burning.
     */
    private function deletePointBurningCartCoupon(CartModel $cart)
    {
        // Looking for coupon code.
        if (!$code = $cart->getQuote()->getCouponCode()) {
            return FALSE;
        }

        if (!$this->_couponHelper->deletePointBurningCoupon($code)) {
            return FALSE;
        }

        $cart->getQuote()->setCouponCode(NULL);
        return TRUE;
    }

    /**
     * @param CartModel $cart
     * @return RuleModel
     * @throws LocalizedException
     */
    protected function createCartRuleWithCoupon(CartModel $cart)
    {
        return $this->_ruleFactory
            ->create()
            ->setName('Loyalty point burning coupon')
            ->setDescription('Loyalty point burning coupon')
            ->setFromDate(
                $this->_dateTimeHelper->gmtDate('Y-m-d')
            )
            ->setToDate(
                $this->_dateTimeHelper->gmtDate(
                    'Y-m-d',
                    strtotime('+14 days')
                )
            )
            ->setCouponType(
                RuleModel::COUPON_TYPE_SPECIFIC
            )
            ->setCouponCode(
                $this->_couponHelper->generatePointBurningCouponCode(
                    $cart->getCustomerSession()->getId()
                )
            )
            ->setUsesPerCoupon(1)
            ->setUsesPerCustomer(1)
            ->setCustomerGroupIds($this->_customerGroupModel->getCollection()->getAllIds())
            ->setIsActive(1)
            ->setConditionsSerialized('')
            ->setActionsSerialized('')
            ->setStopRulesProcessing(0)
            ->setIsAdvanced(1)
            ->setProductIds('')
            ->setSortOrder(1)
            ->setSimpleAction(RuleModel::CART_FIXED_ACTION)
            ->setDiscountStep(0)
            ->setTimesUsed(0)
            ->setIsRss(0)
            ->setWebsiteIds([$this->_storeManager->getStore()->getWebsiteId()]);
    }

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param int $pointsBurned
     */
    public function handleCartCoupon(CartModel $cart, $pointsBurned)
    {
        try {
            $spendable = $this->_customerHelper->getSpendablePoints();

            if ($spendable <= 0) {
                $this->setPointsBurned(0);
                return;
            }

            // Point burning explicitly turned off.
            if (isset($pointsBurned) && 0 == $pointsBurned) {
                $this->setPointsBurned(0);
                // Removing Antavo point burning coupon, if there is one.
                $this->deletePointBurningCartCoupon($cart);
                return;
            }

            // Turning off point burning when cart has a different coupon set.
            if ($code = $cart->getQuote()->getCouponCode()) {
                if (!$this->_couponHelper->isPointBurningCouponCode($code)) {
                    $this->setPointsBurned(0);
                    return;
                }
            }

            // Getting the current rule or creating a new one
            if ($rule = $this->getCartCouponRule($cart)) {
                $couponCode = $rule->getPrimaryCoupon()->getCode();
            } else {
                $rule = $this->createCartRuleWithCoupon($cart);
                $couponCode = $rule->getCouponCode();
            }

            // If there is no explicit points burned value defined,
            // calculating with the customer's maximum spendable points
            if (!isset($pointsBurned)) {
                $pointsBurned = $spendable;
            }

            // If the spendable points is less than the points burned value,
            // capping that to the maximum spendable points
            if ($spendable < $pointsBurned) {
                $pointsBurned = $spendable;
            }

            // Maximizing points burned value to the limit of the
            // maximum redeemable points
            if ($limit = $this->getPointRedeemLimit()) {
                $pointsBurned = min($pointsBurned, $limit);
            }

            // Calculating coupon value.
            $couponValue = $this->calculateCouponValue($pointsBurned);
            $cartTotal = $this->getCartTotal($cart);

            if ($couponValue > $cartTotal) {
                $couponValue = $cartTotal;
                $pointsBurned = $this->_couponHelper->calculatePointsBurned($couponValue);
            }

            $this->setPointsBurned($pointsBurned);
            $rule->setCouponCode($couponCode)->setDiscountAmount($couponValue)->save();
            $cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
        } catch (\Exception $e) {
            // Failing silently...
        }
    }
}
