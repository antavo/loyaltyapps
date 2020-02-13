<?php
namespace Antavo\LoyaltyApps\Helper\Environments;

/**
 *
 */
class Development extends AbstractEnvironment
{
    /**
     * @inheritdoc
     */
    protected $_clientId = '2546875357658765';

    /**
     * @inheritdoc
     */
    protected $_clientSecret = '749184fbfa63c0edef9876a69939aa981f8e181a';

    /**
     * @inheritdoc
     */
    protected $_apiUrl = 'http://api-apps-dev.antavo.local';

    /**
     * @inheritdoc
     */
    protected $_sdkUrl = 'http://api-apps-dev.antavo.local/sdk';
}
