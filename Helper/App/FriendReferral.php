<?php
namespace Antavo\LoyaltyApps\Helper\App;

use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SalesRule\Model\Coupon as CouponModel;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeHelper;
use Magento\Customer\Model\Group as CustomerGroupModel;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Math\Random as MathRandomHelper;
use Magento\SalesRule\Model\CouponFactory as CouponFactory;
use Magento\SalesRule\Model\RuleFactory as RuleFactory;

/**
 *
 */
class FriendReferral implements AppInterface
{
    /**
     * Name of the friend referral cookie (holding the referrer customer's ID).
     *
     * @var string
     */
    const COOKIE_NAME = '__alr';

    /**
     * Storing friend referral price rule name in a constant
     *
     * @var string
     */
    const LOYALTY_FRIEND_REFERRAL_RULE = 'Loyalty friend referral rule';

    /**
     * @var string
     */
    const XML_PATH_ENABLED = 'antavo_loyaltyapps/friendreferral/enabled';

    /**
     * @var string
     */
    const XML_PATH_URL = 'antavo_loyaltyapps/friendreferral/url';

    /**
     * @var string
     */
    const XML_PATH_FRIEND_REFERRAL_RULE_ID = 'antavo_loyaltyapps/friendreferral/rule_id';

    /**
     * List of enabled channels for friend referral stored under this config
     * key as comma separated values.
     *
     * @var string
     */
    const XML_PATH_CHANNELS = 'antavo_loyaltyapps/friendreferral/channel';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_dateTimeHelper;

    /**
     * @var \Magento\Customer\Model\Group
     */
    private $_customerGroupModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $_mathRandomHelper;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    private $_couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $_ruleFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeHelper
     * @param \Magento\Customer\Model\Group $customerGroupModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Math\Random $mathRandomHelper
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTimeHelper $dateTimeHelper,
        CustomerGroupModel $customerGroupModel,
        StoreManagerInterface $storeManager,
        MathRandomHelper $mathRandomHelper,
        CouponFactory $couponFactory,
        RuleFactory $ruleFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_dateTimeHelper = $dateTimeHelper;
        $this->_customerGroupModel = $customerGroupModel;
        $this->_storeManager = $storeManager;
        $this->_mathRandomHelper = $mathRandomHelper;
        $this->_couponFactory = $couponFactory;
        $this->_ruleFactory = $ruleFactory;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * Returns list of enabled channels for friend referral.
     *
     * @return array
     */
    public function getChannels()
    {
        return preg_split(
            '/\s*,\s*/',
            trim($this->_scopeConfig->getValue(self::XML_PATH_CHANNELS)),
            0,
            PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * Checks if code was issued for a friend referral.
     *
     * @param string $code
     * @return bool
     */
    public function isFriendReferralCouponCode($code)
    {
        return preg_match(
            '/^' . $this->getFriendReferralCouponCodePrefix() . '-\d{1,3}-\d{6}-[A-Z\d]+$/',
            $code
        );
    }

    /**
     * Tells if given channel enabled for friend referral.
     *
     * @param string $channel
     * @return bool
     */
    public function hasChannel($channel)
    {
        return in_array($channel, $this->getChannels());
    }


    /**
     * @return array
     */
    private function getFriendReferralCustomerGroups()
    {
        $groups = $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_FRIEND_REFERRAL_DISCOUNT_GROUPS
        );

        if (!$groups) {
            return $this->_customerGroupModel->getCollection()->getAllIds();
        }

        return preg_split(
            '/\s*,\s*/',
            trim($groups),
            0,
            PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * @return array
     */
    private function getFriendReferralWebsites()
    {
        $websites = $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_FRIEND_REFERRAL_DISCOUNT_WEBSITES
        );

        if (!$websites) {
            return array_reduce(
                $this->_storeManager->getWebsites(TRUE),
                function (array $carry, WebsiteInterface $website) {
                    $carry[] = $website->getId();
                    return $carry;
                },
                []
            );
        }

        return preg_split(
            '/\s*,\s*/',
            trim($websites),
            0,
            PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * @return array
     */
    private function getFriendReferralCustomerSegments()
    {
        $segments = $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_FRIEND_REFERRAL_DISCOUNT_SEGMENTS
        );

        if ($segments) {
            return preg_split('/\s*,\s*/', trim($segments), 0, PREG_SPLIT_NO_EMPTY);
        }

        return NULL;
    }

    /**
     * @param array $segments
     * @return array
     */
    private function createSegmentConditions(array $segments) {
        $conditions = [
            1 => [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => '',
            ],
        ];

        foreach ($segments as $i => $segment) {
            $conditions['1--' . $i] = [
                'type' => 'Magento\\CustomerSegment\\Model\\Segment\\Condition\\Segment',
                'operator' => '==',
                'value' => $segment,
            ];
        }

        return $conditions;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $priceRule
     * @param int $percentage
     * @return mixed
     */
    public function handlePriceRuleCustomSettings($priceRule, $percentage)
    {
        $priceRule
            ->setCustomerGroupIds($this->getFriendReferralCustomerGroups())
            ->setWebsiteIds($this->getFriendReferralWebsites())
            ->setDiscountAmount($percentage);

        if ($segments = $this->getFriendReferralCustomerSegments()) {
            $priceRule->loadPost(
                [
                    'conditions' => $this->createSegmentConditions($segments),
                ]
            );
        }

        return $priceRule;
    }

    /**
     * Creates a friend referral price rule with given percentage discount.
     *
     * @param int $percentage
     * @return \Magento\SalesRule\Model\Rule
     */
    public function createFriendReferralPriceRule($percentage)
    {
        try {
            $rule = $this->_ruleFactory
                ->create()
                ->setName(self::LOYALTY_FRIEND_REFERRAL_RULE)
                ->setDescription(self::LOYALTY_FRIEND_REFERRAL_RULE)
                ->setCouponType(RuleModel::COUPON_TYPE_SPECIFIC)
                ->setUseAutoGeneration(TRUE)
                ->setUsesPerCoupon(1)
                ->setDiscountQty(0)
                ->setUsesPerCustomer(1)
                ->setIsActive(1)
                ->setIsAdvanced(1)
                ->setProductIds('')
                ->setSortOrder(1)
                ->setSimpleAction(RuleModel::BY_PERCENT_ACTION)
                ->setDiscountStep(0)
                ->setTimesUsed(0)
                ->setIsRss(0);
            $rule = $this->handlePriceRuleCustomSettings($rule, $percentage);
            return $rule->save();
        } catch (\Exception $e) {
            // Failing silently...
        }

        return NULL;
    }

    /**
     * Percentage is an int between 1 and 100
     *
     * @param \Magento\SalesRule\Model\Rule $priceRule
     * @param int $percentage
     * @return \Magento\SalesRule\Model\Coupon
     */
    public function createFriendReferralCoupon(RuleModel $priceRule, $percentage) {
        try {
            return $this->_couponFactory
                ->create()
                ->setRule($priceRule)
                ->setCreatedAt($this->_dateTimeHelper->gmtDate('Y-m-d'))
                ->setCode($this->generateFriendReferralCouponCode($percentage))
                ->setUsageLimit(1)
                ->setType(CouponModel::TYPE_GENERATED)
                ->save();
        } catch (\Exception $e) {
            // Failing silently...
        }

        return NULL;
    }

    /**
     * Since coupon code prefix can be set manually this getter returns it's value.
     * Default is ANTFR if not set anything different.
     *
     * @return mixed|string
     */
    public function getFriendReferralCouponCodePrefix()
    {
        $prefix = $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_FRIEND_REFERRAL_DISCOUNT_CODE_PREFIX
        );
        return $prefix ?: 'ANTFR';
    }

    /**
     * Generates a safe random coupon code for discount percentage.
     *
     * Coupon code can be matched with the following regexp:
     * (The prefix might receive from setting)
     * /^ANTFR-(\d+)-(\d{6})-[A-Z\d]{6}$/, where the first capture is the
     * discount percentage and the second one is the creation date in "ymd" format.
     *
     * @param int $percentage
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateFriendReferralCouponCode($percentage)
    {
        return sprintf(
            $this->getFriendReferralCouponCodePrefix() . '-%d-%d-%s',
            $percentage,
            $this->_dateTimeHelper->gmtDate('ymd'),
            $this->_mathRandomHelper->getRandomString(
                6,
                MathRandomHelper::CHARS_UPPERS . MathRandomHelper::CHARS_DIGITS
            )
        );
    }
}
