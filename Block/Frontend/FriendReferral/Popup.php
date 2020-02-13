<?php
namespace Antavo\LoyaltyApps\Block\Frontend\FriendReferral;

use Antavo\LoyaltyApps\Helper\App\FriendReferral as FriendReferralHelper;
use Magento\Framework\View\Element\Template;

/**
 *
 */
class Popup extends Template
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\App\FriendReferral
     */
    protected $_friendReferralHelper;

    /**
     * Returns the URL of the friend referral application.
     *
     * @return string
     */
    public function getFriendReferralUrl()
    {
        return sprintf(
            '%s/invite',
            $this->_scopeConfig->getValue(
                FriendReferralHelper::XML_PATH_URL
            )
        );
    }

    /**
     * @param string $channel
     * @return bool
     */
    public function hasFriendReferralChannel($channel) {
        return $this->_friendReferralHelper->hasChannel($channel);
    }

    /**
     * @param Template\Context $context
     * @param \Antavo\LoyaltyApps\Helper\App\FriendReferral $friendReferralHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FriendReferralHelper $friendReferralHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_friendReferralHelper = $friendReferralHelper;
    }
}
