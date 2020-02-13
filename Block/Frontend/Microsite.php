<?php
namespace Antavo\LoyaltyApps\Block\Frontend;

use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Magento\Framework\View\Element\Template;

/**
 *
 */
class Microsite extends Template
{
    /**
     * Returns the microsite URL for the configured brand.
     * It may be a custom domain URL, or a test Antavo URL.
     *
     * @return string
     */
    public function getMicrositeUrl()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_LOYALTY_CENTRAL_URL
        ) ?: NULL;
    }
}
