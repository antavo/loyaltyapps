<?php
namespace Antavo\LoyaltyApps\Block\Frontend;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\Config as ConfigHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Antavo\LoyaltyApps\Helper\SourceModels\CustomerAuthentication;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * @property \Magento\Customer\Model\Session $_session
 * @method \Magento\Framework\App\Request\Http getRequest()
 */
class JsSdk extends Template
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Config
     */
    protected $_configHelper;

    /**
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->_configHelper->getEnvironment()->getSdkUrl();
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(ApiClient::XML_PATH_API_KEY);
    }

    /**
     * @return string
     */
    public function getAuthenticationMethod()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_PLUGIN_CUSTOMER_AUTHENTICATION
        );
    }

    /**
     * @return string
     */
    public function getSdkHashingMethod()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_JS_SDK_HASH_METHOD
        );
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        if (CustomerAuthentication::AUTHENTICATION_COOKIE == $this->getAuthenticationMethod()) {
            return (string) $this->_session->getCustomerId();
        }

        return NULL;
    }

    /**
     * @return array
     */
    public function getSdkConfiguration()
    {
        return [
            'auth' => [
                'method' => $authMethod = $this->getAuthenticationMethod(),
                'cookie' => CustomerAuthentication::AUTHENTICATION_COOKIE == $authMethod,
            ],
            'video' => [
                'enabled' => TRUE,
            ],
            'social' => [
                'enabled' => TRUE,
            ],
            'notifications' => FALSE,
            'socialShare' => [
                'enabled' => $this->isSocialShareEnabled(),
            ],
            'tracking' => [
                'hashMethod' => $this->getSdkHashingMethod(),
            ],
        ];
    }

    /**
     * @param Template\Context $context
     * @param \Antavo\LoyaltyApps\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
    }

    /**
     * Returns a boolean for enabling/disabling JS SDK injection.
     * It's a temporary variable to avoid JS failures.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $result = TRUE;
        $requestUri = $this->getRequest()->getRequestUri();

        if (preg_match('/checkout/', $requestUri)) {
            $result = FALSE;
        }

        if (preg_match('/checkout\/cart/', $requestUri)) {
            $result = TRUE;
        }

        if (preg_match('/checkout\/onepage\/success/', $requestUri)) {
            $result = TRUE;
        }

        return $result;
    }

    /**
     * Returns a boolean value for determining the state of social share extension.
     *
     * @return bool
     */
    public function isSocialShareEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_SOCIAL_SHARE_ENABLED_STORES,
            ScopeInterface::SCOPE_STORES
        );
    }
}
