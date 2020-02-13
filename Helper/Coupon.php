<?php
namespace Antavo\LoyaltyApps\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SalesRule\Model\Coupon as CouponModel;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Math\Random as MathRandomHelper;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeHelper;

/**
 *
 */
class Coupon
{
    /**
     * @var string
     */
    const DEFAULT_COUPON_CODE_PREFIX = 'ANTPB';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    private $_couponModel;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    private $_ruleModel;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $_mathRandomHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_dateTimeHelper;

    /**
     * Since coupon code prefix can be set manually this getter returns it's value.
     * Default is ANTPB if not set anything different.
     *
     * @return string
     */
    public function getCheckoutCouponCodePrefix()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_CHECKOUT_DISCOUNT_CODE_PREFIX
        ) ?: self::DEFAULT_COUPON_CODE_PREFIX;
    }

    /**
     * Checks if code was issued with a point burning coupon.
     *
     * @param string $code
     * @return bool
     */
    public function isPointBurningCouponCode($code)
    {
        return preg_match('/^' . $this->getCheckoutCouponCodePrefix() . '-\d+-\d{6}-[A-Z\d]+$/', $code);
    }

    /**
     * Generates a safe random coupon code for customer.
     *
     * Coupon code can be matched with the following regexp:
     * (The prefix might receive from setting)
     * /^ANTPB-(\d+)-(\d{6})-[A-Z\d]{6}$/, where the first capture is the
     * customer ID and the second one is the creation date in "ymd" format.
     *
     * @param $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generatePointBurningCouponCode($customerId)
    {
        return sprintf(
            $this->getCheckoutCouponCodePrefix() . '-%d-%d-%s',
            $customerId,
            $this->_dateTimeHelper->gmtDate('ymd'),
            $this->_mathRandomHelper->getRandomString(
                6,
                MathRandomHelper::CHARS_UPPERS . MathRandomHelper::CHARS_DIGITS
            )
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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @param \Magento\SalesRule\Model\Coupon $couponModel
     * @param \Magento\Framework\Math\Random $mathRandomHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RuleModel $ruleModel,
        CouponModel $couponModel,
        MathRandomHelper $mathRandomHelper,
        DateTimeHelper $dateTimeHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_ruleModel = $ruleModel;
        $this->_couponModel = $couponModel;
        $this->_mathRandomHelper = $mathRandomHelper;
        $this->_dateTimeHelper = $dateTimeHelper;
    }

    /**
     * Removes Antavo point burning coupon, if there is one.
     *
     * @param $code
     * @return bool Returns TRUE if coupon was for point burning, FALSE
     * otherwise (in which case no deletion happens).
     */
    public function deletePointBurningCoupon($code)
    {
        try {
            // Checking if code belongs to a point burning coupon.
            if (!$this->isPointBurningCouponCode($code)) {
                return FALSE;
            }

            $coupon = $this->_couponModel->load($code, 'code');

            if ($coupon->getId()) {
                /** @var \Magento\SalesRule\Model\Rule $rule */
                $rule = $this->_ruleModel->load($coupon->getRuleId());

                if ($rule->getId()) {
                    $rule->delete();
                }
            }

            return TRUE;
        } catch (\Exception $e) {
            // Failing silently...
        }
    }
}
