<?php
namespace Antavo\LoyaltyApps\Helper\Environments;

/**
 *
 */
abstract class AbstractEnvironment
{
    /**
     * @var string
     */
    protected $_sdkUrl;

    /**
     * @var string
     */
    protected $_apiUrl;

    /**
     * @var string
     */
    protected $_clientId;

    /**
     * @var string
     */
    protected $_clientSecret;

    /**
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->_sdkUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->_apiUrl;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }
}
