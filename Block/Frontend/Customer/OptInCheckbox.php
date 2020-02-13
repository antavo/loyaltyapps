<?php
namespace Antavo\LoyaltyApps\Block\Frontend\Customer;

use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 *
 */
class OptInCheckbox extends Template
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_CUSTOMER_OPTIN_EVENT,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }
}
