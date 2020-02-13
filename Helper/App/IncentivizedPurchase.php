<?php
namespace Antavo\LoyaltyApps\Helper\App;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 *
 */
class IncentivizedPurchase implements AppInterface
{
    /**
     * @var string
     */
    const XML_PATH_ENABLED = 'antavo_loyaltyapps/incentivizedpurchase/enabled';

    /**
     * @var string
     */
    const XML_PATH_PERCENTAGE = 'antavo_loyaltyapps/incentivizedpurchase/percentage';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }
}
