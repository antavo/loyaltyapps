<?php
namespace Antavo\LoyaltyApps\Controller\Friendreferral;

use Antavo\LoyaltyApps\Controller\ControllerTrait;
use Antavo\LoyaltyApps\Controller\ServiceControllerTrait;
use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\App\FriendReferral as FriendReferralHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriterInterface;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\SalesRule\Model\RuleFactory;

/**
 *
 */
class Coupon extends Action
{
    use ControllerTrait;
    use ServiceControllerTrait;

    /**
     * @var \Antavo\LoyaltyApps\Helper\App\FriendReferral
     */
    private $_friendReferralHelper;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    private $_ruleModel;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $_ruleFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $_configWriter;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Antavo\LoyaltyApps\Helper\App\FriendReferral $friendReferralHelper
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        Context $context,
        FriendReferralHelper $friendReferralHelper,
        RuleModel $ruleModel,
        RuleFactory $ruleFactory,
        ScopeConfigInterface $scopeConfig,
        ConfigWriterInterface $configWriter
    ) {
        parent::__construct($context);
        $this->_friendReferralHelper = $friendReferralHelper;
        $this->_ruleModel = $ruleModel;
        $this->_ruleFactory = $ruleFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_configWriter = $configWriter;
    }

    /**
     * Handles (POST) requests for new friend referral coupons.
     *
     * @inheritdoc
     */
    public function execute()
    {
        try {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->getRequest();

            if (!$request->isPost()) {
                $this->displayNotFound();
                return;
            }

            // Checking if all parameters are received: API key & coupon percentage.
            if (!$apiKey = $request->getPost('api_key')) {
                throw new \RuntimeException('Missing required parameter \'api_key\'');
            }

            if (!$percentage = $request->getPost('coupon_percentage')) {
                throw new \RuntimeException('Missing required parameter \'coupon_percentage\'');
            }

            // Validating parameters.
            if ($this->_scopeConfig->getValue(ApiClient::XML_PATH_API_KEY) != $apiKey) {
                throw new \RuntimeException('Invalid API key');
            }

            if (!preg_match('/^\d+$/', $percentage) || $percentage < 1 || $percentage > 100) {
                throw new \RuntimeException('Coupon percentage should be an integer between 1 and 100');
            }

            $priceRuleId = $this->_scopeConfig->getValue(
                FriendReferralHelper::XML_PATH_FRIEND_REFERRAL_RULE_ID
            );

            if ($priceRuleId) {
                $priceRule = $this
                    ->_ruleModel
                    ->load($priceRuleId);
            } else {
                $priceRule = $this
                    ->_friendReferralHelper
                    ->createFriendReferralPriceRule($percentage);

                // Saving the created friend referral rule to the config
                $this->_configWriter->save(
                    FriendReferralHelper::XML_PATH_FRIEND_REFERRAL_RULE_ID,
                    $priceRule->getId()
                );
            }

            $this->sendJsonResponse(
                [
                    'code' => $this->_friendReferralHelper
                        ->createFriendReferralCoupon($priceRule, $percentage)
                        ->getCode(),
                ]
            );
        } catch (\RuntimeException $e) {
            $this->sendJsonResponse(
                $this->getErrorResponse($e),
                400
            );
        } catch (\Exception $e) {
            $this->sendJsonResponse(
                $this->getErrorResponse($e),
                500
            );
        }
    }
}
