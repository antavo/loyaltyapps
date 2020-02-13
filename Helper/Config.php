<?php
namespace Antavo\LoyaltyApps\Helper;

use Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 *
 */
class Config
{
    /**
     * @var string
     */
    const XML_PATH_PLUGIN_ENVIRONMENT = 'antavo_loyaltyapps/core/environment';

    /**
     * @var string
     */
    const ENVIRONMENT_DEVELOPMENT = 'development';

    /**
     * @var string
     */
    const ENVIRONMENT_TESTING = 'testing';

    /**
     * @var string
     */
    const ENVIRONMENT_RELEASE = 'release';

    /**
     * @var string
     */
    const ENVIRONMENT_DEMO = 'demo';

    /**
     * @var string
     */
    const ENVIRONMENT_LOYALTY_STACK1 = 'loyaltyStack1';

    /**
     * @var string
     */
    const ENVIRONMENT_LOYALTY_STACK2 = 'loyaltyStack2';

    /**
     * @var string
     */
    const DEFAULT_ENVIRONMENT = self::ENVIRONMENT_LOYALTY_STACK1;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment
     */
    protected $_environment;

    /**
     * @return \Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * @param \Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment $environment
     * @return $this
     */
    public function setEnvironment(AbstractEnvironment $environment)
    {
        $this->_environment = $environment;
        return $this;
    }

    /**
     * @return array
     * @static
     */
    public static function getEnvironmentSelection()
    {
        return [
            self::ENVIRONMENT_DEVELOPMENT,
            self::ENVIRONMENT_TESTING,
            self::ENVIRONMENT_RELEASE,
            self::ENVIRONMENT_DEMO,
            self::ENVIRONMENT_LOYALTY_STACK1,
            self::ENVIRONMENT_LOYALTY_STACK2
        ];
    }

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;

        if (!$environment = $scopeConfig->getValue(self::XML_PATH_PLUGIN_ENVIRONMENT)) {
            $environment = self::DEFAULT_ENVIRONMENT;
        }

        $class = '\Antavo\LoyaltyApps\Helper\Environments\\' . ucfirst($environment);
        $this->setEnvironment(new $class);
    }
}
