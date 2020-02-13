<?php
namespace Antavo\LoyaltyApps\Helper\Customer;

use Antavo\LoyaltyApps\Helper\Cookie as CookieHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Antavo\LoyaltyApps\Helper\SourceModels\CustomerAuthentication;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * This class handles one main Magento event:
 *  - customer_logout
 */
class LogoutObserver implements ObserverInterface
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Cookie
     */
    private $_cookieHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * Determines the social authentication method from scope's configuration.
     *
     * @return string
     */
    private function getAuthenticationMethod()
    {
        return $this->_scopeConfig->getValue(
            AntavoConfigInterface::XML_PATH_PLUGIN_CUSTOMER_AUTHENTICATION
        );
    }

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Antavo\LoyaltyApps\Helper\Cookie $cookieHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CookieHelper $cookieHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_cookieHelper = $cookieHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        // If the plugin is not enabled yet, return
        if (!$this->_scopeConfig->getValue(AntavoConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
            return TRUE;
        }

        // If the authentication method is "social", unset the "__alc" cookie
        // after customer log out
        if (CustomerAuthentication::AUTHENTICATION_SOCIAL == $this->getAuthenticationMethod()) {
            $this->_cookieHelper->delete('__alc');
        }

        return TRUE;
    }
}
