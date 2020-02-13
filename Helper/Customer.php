<?php
namespace Antavo\LoyaltyApps\Helper;

use Antavo\LoyaltyApps\Helper\Cookie as CookieHelper;
use Antavo\LoyaltyApps\Helper\Token\CustomerToken;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Customer\Model\Customer as CustomerModel;

/**
 *
 */
class Customer
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $_customerModel;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cookie
     */
    private $_cookieHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Antavo\LoyaltyApps\Helper\Cookie $cookieHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        CustomerModel $customerModel,
        ApiClient $apiClient,
        CookieHelper $cookieHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        $this->_apiClient = $apiClient;
        $this->_cookieHelper = $cookieHelper;
    }

    /**
     * @param string $customer_id
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException If cookie couldn't be sent to the browser.
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws \Magento\Framework\Exception\InputException If the cookie name is empty or contains invalid characters.
     */
    public function authenticateCustomer($customer_id)
    {
        $api_secret = $this->_scopeConfig->getValue(
            ApiClient::XML_PATH_API_SECRET
        );

        $this->_cookieHelper->set(
            '__alc',
            (string) (new CustomerToken($api_secret))->setData(
                [
                    'customer' => $customer_id,
                ]
            )
        );
    }

    /**
     * Fetches customer loyalty data via API.
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return array
     */
    private function fetchData(CustomerModel $customer)
    {
        try {
            $result = $this->_apiClient->get(
                sprintf(
                    'auth?%s',
                    http_build_query(['customer' => $customer->getId()])
                )
            );

            if (is_array($result)) {
                foreach ($result as $item) {
                    if (isset($item->type) && 'customer' == $item->type) {
                        return (array) $item->data;
                    }
                }
            }

            // Fallback value.
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Returns customer (currently in session) loyalty data/property.
     *
     * @param string $key  Property name (optional).
     * @return mixed  Property value or the whole data in a {@see Varien_Object}
     * if *$key* is omitted.
     */
    public function getData($key = '')
    {
        if (!isset($this->_data)) {
            $session = $this->_customerSession;
            $this->_data = new DataObject(
                $session->isLoggedIn()
                    ? $this->fetchData($session->getCustomer())
                    : []
            );
        }

        if ($key) {
            return $this->_data->getData($key);
        }

        return $this->_data;
    }

    /**
     * Returns number of points the customer (currently in session) can spend.
     *
     * @return int
     */
    public function getSpendablePoints()
    {
        $data = $this->getData();
        return $data['score'] - $data['spent'] - $data['reserved'];
    }

    /**
     * Returns the spendable points of customer by sending a request to Antavo
     * If the request falls, or customer has no spendable points, it returns 0
     *
     * Don't use it if it's not necessary!
     *
     * Use self::getSpendablePoints() instead
     *
     * Customer can be fetched form DB by id, it's optional
     *
     * @param int $customerId
     * @return int
     */
    public function getSpendablePointsFromRequest($customerId = NULL)
    {
        if (!isset($customerId)) {
            $customerId = $this->_customerSession->getCustomerId();
        }

        try {
            $baseData = $this->_apiClient->get(
                $this->_apiClient->getBaseUri()
                    . '/auth?api_key='
                    . $this->_apiClient->getKey()
                    . '&customer=' . $customerId
            );
            $data = $baseData[1]->{'data'};
            return $data->{'score'} - $data->{'spent'} - $data->{'reserved'};
        } catch (\Exception $e) {
            // If exception thrown, customer's redeemable point is zero
            return 0;
        }
    }
}
