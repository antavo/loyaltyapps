<?php
namespace Antavo\LoyaltyApps\Helper\Checkout;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class ReserveHelper {
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return filter_var(
            $this->_scopeConfig->getValue(
                ConfigInterface::XML_PATH_RESERVE_BURNED_POINTS,
                ScopeInterface::SCOPE_STORES,
                $this->_storeManager->getStore()->getId()
            ),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ApiClient $apiClient
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_apiClient = $apiClient;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param int $points
     * @throws \Exception
     */
    public function reserve(Order $order, $points)
    {
        try {
            // If the plugin is not enabled yet, return
            if (!$this->_scopeConfig->getValue(ConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
                return;
            }

            // If the reserve mechanism is not enabled, return
            if (!$this->isEnabled()) {
                return;
            }

            // If the order is a guest checkout, return
            if (!$order->getCustomerId()) {
                return;
            }

            // If there is no burned points, return
            if (!is_int($points) || $points <= 0) {
                return;
            }

            // Sending checkout event via API client.
            $this->_apiClient->sendEvent(
                $order->getCustomerId(),
                'reserve_points',
                [
                    'transaction_id' => $order->getId(),
                    'points' => $points,
                ]
            );
        } catch (\Exception $e) {
            $this->_apiClient->setError($e);
            throw $e;
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @throws \Exception
     */
    public function release(Order $order)
    {
        try {
            // If the plugin is not enabled yet, return
            if (!$this->_scopeConfig->getValue(ConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
                return;
            }

            // If the release mechanism is not enabled, return
            if (!$this->isEnabled()) {
                return;
            }

            // If the order is a guest checkout, return
            if (!$order->getCustomerId()) {
                return;
            }

            // Sending checkout event via API client.
            $this->_apiClient->sendEvent(
                $order->getCustomerId(),
                'release_points',
                [
                    'transaction_id' => $order->getId(),
                ]
            );
        } catch (\Exception $e) {
            $this->_apiClient->setError($e);
            throw $e;
        }
    }
}
