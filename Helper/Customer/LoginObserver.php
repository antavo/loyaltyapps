<?php
namespace Antavo\LoyaltyApps\Helper\Customer;

use Antavo\LoyaltyApps\Helper\Cookie as CookieHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Antavo\LoyaltyApps\Helper\ApiClient as AntavoApiClient;
use Antavo\LoyaltyApps\Helper\SourceModels\CustomerAuthentication;
use Antavo\LoyaltyApps\Helper\Token\CustomerToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class LoginObserver implements ObserverInterface
{
    use CustomerExporterTrait;

    /**
     * @var bool
     */
    protected static $_optedIn = FALSE;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cookie
     */
    private $_cookieHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * @param int $customerId
     * @return \Antavo\LoyaltyApps\Helper\Token\CustomerToken
     */
    private function createCustomerToken($customerId)
    {
        return (new CustomerToken(
            $this->_scopeConfig->getValue(
                AntavoApiClient::XML_PATH_API_SECRET
            )
        ))->setCustomer($customerId);
    }

    /**
     * @param string $customerId
     */
    private function authenticateCustomer($customerId)
    {
        $this->_cookieHelper->set(
            '__alc',
            (string) $this->createCustomerToken($customerId)
        );
    }

    /**
     * @return bool
     */
    private function isLoyaltyCheckboxExists()
    {
        return (bool) strlen(
            $this->_request->getParam('antavo_loyalty')
        );
    }

    /**
     * @return bool
     */
    private function isLoyaltyCheckboxChecked()
    {
        return filter_var(
            $this->_request->getParam('antavo_loyalty'),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
    }

    /**
     * @return bool
     */
    private  function hasLoyaltyConsent()
    {
        if ($this->_cookieHelper->get('enroll_customer')) {
            $this->_cookieHelper->delete('enroll_customer');
            return TRUE;
        }

        // By GDPR we don't send opt in after login so checkbox is required
        // opt in is available just in case the customer ticked the checkbox
        if ($this->isLoyaltyCheckboxExists() && !$this->isLoyaltyCheckboxChecked()) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return bool
     */
    private function isOptinSendingEnabled()
    {
        return $this->_scopeConfig->getValue(
            AntavoConfigInterface::XML_PATH_CUSTOMER_OPTIN_EVENT,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Antavo\LoyaltyApps\Helper\Cookie $cookieHelper
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CookieHelper $cookieHelper,
        AntavoApiClient $apiClient,
        RequestInterface $request
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_cookieHelper = $cookieHelper;
        $this->_apiClient = $apiClient;
        $this->_request = $request;
    }
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        try {
            // If the plugin is not enabled yet, return
            if (!$this->_scopeConfig->getValue(AntavoConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
                return TRUE;
            }

            // If this event hook is already fired in this process, return
            if (self::$_optedIn) {
                return TRUE;
            }

            // If the opt-in event sending is disabled in this store, return
            if (!$this->isOptinSendingEnabled()) {
                return TRUE;
            }

            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getData('customer');

            // Getting the authentication method
            $authentication = $this->_scopeConfig->getValue(
                AntavoConfigInterface::XML_PATH_PLUGIN_CUSTOMER_AUTHENTICATION
            );

            // Setting the "__alc" cookie if the customer authentication method is "social"
            if (CustomerAuthentication::AUTHENTICATION_SOCIAL == $authentication) {
                $this->authenticateCustomer($customer->getId());
            }

            // If enroll cookie is set, loyalty checkbox is not required
            if (!$this->hasLoyaltyConsent()) {
                return TRUE;
            }

            // Sending the opt_in event to the Antavo Events API
            $this->_apiClient->sendEvent(
                $customer->getId(),
                'opt_in',
                $this->exportCustomerProperties($customer)
            );

            // This event hook is fired more than once, so it should be reduced here
            self::$_optedIn = TRUE;
        } catch (\Exception $e) {
            // Failing silently...
        }

        return TRUE;
    }
}
