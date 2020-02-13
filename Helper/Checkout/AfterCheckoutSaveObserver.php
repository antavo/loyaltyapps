<?php
namespace Antavo\LoyaltyApps\Helper\Checkout;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Antavo\LoyaltyApps\Helper\SourceModels\CheckoutSendingType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 *
 */
class AfterCheckoutSaveObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout
     */
    private $_checkoutHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout\ReserveHelper
     */
    private $_reserveHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Antavo\LoyaltyApps\Helper\Checkout $checkoutHelper
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Antavo\LoyaltyApps\Helper\Checkout\ReserveHelper $reserveHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CheckoutHelper $checkoutHelper,
        ApiClient $apiClient,
        ReserveHelper $reserveHelper,
        EventManager $eventManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_apiClient = $apiClient;
        $this->_reserveHelper = $reserveHelper;
        $this->_eventManager = $eventManager;
    }

    /**
     * Prepares checkout request data before sending.
     *
     * @param array $data
     * @return array
     */
    public function prepareCheckoutData(array $data)
    {
        if ($this->_scopeConfig->getValue(ConfigInterface::XML_PATH_GENERIC_POINTS)) {
            $data = $this->_checkoutHelper->applyGenericPoints($data);
        }

        return $data;
    }

    /**
     * Handles the whole checkout syncronization process.
     *
     * @param \Magento\Sales\Model\Order $order
     * @throws \Exception  When checkout event sending was unsuccessfully.
     */
    public function handleAfterCheckoutSave(OrderModel $order)
    {
        try {
            if ($this->_checkoutHelper->getCheckoutSendingType($order) == CheckoutSendingType::TYPE_PURCHASE_COMPLETED) {
                // Releasing the reserved points, if needed
                $this->_reserveHelper->release($order);
            }
        } catch (\Exception $e) {
            // Failing silently...
        }

        try {
            // Dispatching custom Antavo event for digging checkout data from helpers.
            $this->_eventManager->dispatch(
                'antavo_checkout_sent_before',
                ['event_data' => $data = new DataObject] + compact('order')
            );

            // Sending checkout event via API client.
            $this->_apiClient->sendEvent(
                $order->getCustomerId(),
                'checkout',
                $this->prepareCheckoutData($data->getData())
            );
        } catch (\Exception $e) {
            // Failing silently...
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        // Sending checkout event; catching all exceptions.
        try {
            // If the plugin is not enabled yet, return
            if (!$this->_scopeConfig->getValue(ConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
                return;
            }

            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getData('order');

            // order might found in last order instead of observer data
            // (basically if purchase type is payment received)
            if (!$order) {
                $order = $this->_checkoutHelper->getLastOrder();
            }

            // If order has not an identifier, return.
            if (!$order->getRealOrderId()) {
                return;
            }

            if (!$this->_checkoutHelper->isCheckoutEventSendingEnabled($order->getStore()->getId())) {
                return;
            }

            // payment received should not have to be complete nor has any invoice
            if ($this->_checkoutHelper->getCheckoutSendingType($order) != CheckoutSendingType::TYPE_PAYMENT_RECEIVED) {
                // If order is not completed yet, return.
                if ($order->getState() != OrderModel::STATE_COMPLETE) {
                    return;
                }

                // If order has not invoices, return.
                if (!$order->hasInvoices()) {
                    return;
                }
            }

            $this->handleAfterCheckoutSave($order);
        } catch (\Exception $e) {
            // Failing silently...
        }
    }
}
