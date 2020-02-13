<?php
namespace Antavo\LoyaltyApps\Controller\Customer;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\Cookie as CookieHelper;
use Antavo\LoyaltyApps\Helper\Customer as CustomerHelper;
use Antavo\LoyaltyApps\Controller\ControllerTrait;
use Antavo\LoyaltyApps\Helper\Customer\CustomerExporterTrait;
use Antavo\LoyaltyApps\Helper\SourceModels\CustomerAuthentication;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class Optin extends Action
{
    use CustomerExporterTrait;
    use ControllerTrait;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $_context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Customer
     */
    private $_customerHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cookie
     */
    private $_cookieHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * Returns the custom configured opt-in redirect URL from the config pool.
     *
     * @return string
     */
    public function getOptinRedirectUrl()
    {
        return $this->_scopeConfig->getValue(
            ConfigInterface::XML_PATH_CUSTOMER_OPTIN_REDIRECT_URL,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        ) ?: NULL;
    }

    /**
     * Returns the calculated redirect URL: if there is a configured redirect URL in the config,
     * returns that; otherwise, returns the referrer URL.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getOptinRedirectUrl() ?: $this->_context->getRedirect()->getRefererUrl();
    }

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Antavo\LoyaltyApps\Helper\Customer $customerHelper
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Antavo\LoyaltyApps\Helper\Cookie $cookieHelper
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        CustomerHelper $customerHelper,
        ApiClient $apiClient,
        CookieHelper $cookieHelper
    ) {
        parent::__construct($context);
        $this->_context = $context;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customerHelper = $customerHelper;
        $this->_apiClient = $apiClient;
        $this->_cookieHelper = $cookieHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();

        try {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->getRequest();

            // If the request is not a POST one, return
            if (!$request->isPost()) {
                $this->displayNotFound();
                return;
            }

            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $this->_customerSession->getCustomer();

            $authentication = $this->_scopeConfig->getValue(
                ConfigInterface::XML_PATH_PLUGIN_CUSTOMER_AUTHENTICATION
            );

            // If the authentication method is social, explicit authentication
            // should be triggered -- setting the "__alc" cookie from the backend
            if (CustomerAuthentication::AUTHENTICATION_SOCIAL == $authentication) {
                $this->_customerHelper->authenticateCustomer($customer->getId());
            }

            // Clicking to enroll button more than once is possible, so cookie will be reset
            if (!$customer->getId()) {
                $this->_cookieHelper->set('enroll_customer', TRUE);
                /** @var \Magento\Store\Model\Store $store */
                $store = $this->_storeManager->getStore();

                // Redirecting customer to the login page
                $response
                    ->setRedirect($store->getUrl('customer/account/login'))
                    ->sendHeaders()
                    ->send();
                return;
            }

            // Calculating customer data for the API request
            $properties = $this->exportCustomerProperties($customer);

            if (isset($properties['email'])) {
                // Sending in the opt_in event to the Events API
                $this->_apiClient->sendEvent($customer->getId(), 'opt_in', $properties);
            }
        } catch (\Exception $e) {
            // Transforming exception messages to flashSession errors
            $this->_context->getMessageManager()->addErrorMessage($e->getMessage());
        } finally {
            // At end of the flow, we should redirect the customer to the
            // calculated redirect URL -- it is configurable on the admin side
            $response
                ->setRedirect($this->getRedirectUrl())
                ->sendHeaders()
                ->send();
            return;
        }
    }
}
