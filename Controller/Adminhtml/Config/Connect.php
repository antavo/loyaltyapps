<?php
namespace Antavo\LoyaltyApps\Controller\Adminhtml\Config;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriterInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store as StoreModel;

/**
 *
 */
class Connect extends Action
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $_productMetadata;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $_configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @return string
     */
    public function getPointMechanism()
    {
        return $this->_scopeConfig->getValue(ConfigInterface::XML_PATH_POINT_MECHANISM);
    }

    /**
     * @param Action\Context $context
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Action\Context $context,
        ApiClient $apiClient,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        ConfigWriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_apiClient = $apiClient;
        $this->_storeManager = $storeManager;
        $this->_productMetadata = $productMetadata;
        $this->_configWriter = $configWriter;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @throws \Exception
     */
    public function sendPluginSettings(StoreModel $store)
    {
        $this->_apiClient->post(
            '/plugin',
            [
                'currency' => $store->getCurrentCurrencyCode(),
                'login_url' => $store->getUrl('customer/account/login'),
                'signup_url' => $store->getUrl('customer/account/create'),
                'account_url' => $store->getUrl('customer/account'),
                'loyalty_central_url' => $store->getUrl('loyalty-center'),
                'website' => $store->getBaseUrl(),
                'friendreferral' => [
                    'callback_url' => $store->getUrl('loyalty-services/friendreferral/coupon')
                ],
                'webshop_engine' => sprintf(
                    '%s %s %s',
                    $this->_productMetadata->getName(),
                    $this->_productMetadata->getVersion(),
                    $this->_productMetadata->getEdition()
                ),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();

        try {
            // Step 1: getting access token using code.
            $result = $this->_apiClient->getOAuthAccessToken(
                $request->getQuery('code'),
                $request->getRequestUri()
            );

            // Step 2: with access token we can finally get API credentials.
            $credentials = $this->_apiClient->post(
                '/plugin/connect?access_token=' . $result->{'access_token'},
                ['domain' => $request->getHttpHost()]
            );

            // Collecting configuration in a few rounds.
            $config = [
                ApiClient::XML_PATH_API_KEY => $credentials->{'api_key'},
                ApiClient::XML_PATH_API_SECRET => $credentials->{'api_secret'},
                ConfigInterface::XML_PATH_PLUGIN_ENABLED => 1,
            ];

            // Persisting API key and secret for subsequent requests
            $this->_apiClient->setKey($credentials->{'api_key'})->setSecret($credentials->{'api_secret'});

            /** @var \Magento\Store\Model\Store $store */
            $store = $this->_storeManager->getStore();

            // Step 3: sending store settings to Antavo.
            $this->sendPluginSettings($store);

            // Step 4: getting plugin settings.
            $result = $this->_apiClient->get('/plugin');

            $config += [
                'antavo_loyaltyapps/core/exchange_rate' => $exchange_rate = $result->{'exchange_rate'},
                'antavo_loyaltyapps/core/loyalty_central_url' => $result->{'loyalty_central_url'},
                'antavo_loyaltyapps/core/loyalty_platform_url' => $result->{'loyalty_platform_url'},
            ];

            // remove all reward module exchange rate and creates a new one with Loyalty exchange rate
            // if reward mechanism is 'using rewards'
            if (PointMechanismType::USING_REWARDS == $this->getPointMechanism()) {
                $this->setRewardModuleExchangeRate($exchange_rate);
            }

            foreach ($result->extensions as $name => $settings) {
                foreach ($settings as $key => $val) {
                    $config['antavo_loyaltyapps/' . $name . '/' . $key] = $val;
                }
            }

            // Saving API credentials and plugin settings.
            $this->saveConfig($config);

            $this->messageManager->addSuccess('The configuration has been saved.');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $response
            ->setRedirect($this->getUrl(
                'admin/system_config/edit/section/antavo_loyaltyapps'
            ))
            ->sendResponse();
    }

    /**
     * Saves plugin configuration.
     *
     * @param array $config  Configuration key-value pairs.
     * @return $this
     */
    protected function saveConfig(array $config)
    {
        foreach ($config as $key => $val) {
            if (preg_match('#^antavo_loyaltyapps/#', $key)) {
                $this->_configWriter->save($key, $val);
            }
        }

        return $this;
    }

    /**
     * @return \Magento\Framework\App\ObjectManager
     */
    private function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Remove all existing reward module exchange rate and set a new one
     * with Loyalty brand's exchange rate.
     *
     * @param float $exchange_rate
     * @throws \Exception  When rate model delete is cannot be completed
     */
    protected function setRewardModuleExchangeRate($exchange_rate)
    {
        $rateModel = $this->getObjectManager()->get('\Magento\Reward\Model\Reward\Rate');

        // Deleting all existing rate model entity
        foreach ($rateModel->getCollection()->getAllIds() as $id) {
            $rateModel->load($id)->delete();
        }

        // Creating the first exchange rate
        $this
            ->getObjectManager()
            ->create('Magento\Reward\Model\Reward\Rate')
            ->addData(
                [
                    'website_id' => 0, // all website globally
                    'customer_group_id' => 0, // all customer group
                    'direction' => 1, // change: points to currency
                    'value' => 1, // rate from
                    'equal_value' => $exchange_rate, // rate to
                ]
            )
            ->save();

        // Creating the second exchange rate
        $this
            ->getObjectManager()
            ->create('Magento\Reward\Model\Reward\Rate')
            ->addData(
                [
                    'website_id' => 0, // all website globally
                    'customer_group_id' => 0, // all customer group
                    'direction' => 2, // change: currency to points
                    'value' => $exchange_rate, // rate to
                    'equal_value' => 1, // rate from
                ]
            )
            ->save();
    }
}
