<?php
namespace Antavo\LoyaltyApps\Helper\Checkout;

use Antavo\LoyaltyApps\Helper\Customer as CustomerHelper;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface;
use Antavo\LoyaltyApps\Helper\Data as DataHelper;
use Antavo\LoyaltyApps\Helper\SourceModels\CheckoutSendingType;
use Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Framework\App\State as StateHelper;

/**
 *
 */
class CheckoutDataObserver implements ObserverInterface
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Customer
     */
    private $_customerHelper;

    /**
     * @var \Magento\Framework\App\State
     */
    private $_stateHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Data
     */
    private $_dataHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout
     */
    private $_checkoutHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Antavo\LoyaltyApps\Helper\Customer $customerHelper
     * @param \Antavo\LoyaltyApps\Helper\Checkout $checkoutHelper
     * @param \Magento\Framework\App\State $stateHelper
     * @param \Antavo\LoyaltyApps\Helper\Data $dataHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerHelper $customerHelper,
        CheckoutHelper $checkoutHelper,
        StateHelper $stateHelper,
        DataHelper $dataHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerHelper = $customerHelper;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_stateHelper = $stateHelper;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function createDefaultCheckoutData(OrderModel $order)
    {
        return [
            'total' => 0,
            'transaction_id' => $order->getRealOrderId(),
            'currency' => $this->_checkoutHelper->getOrderCurrency($order),
            'items' => [],
            'discount' => 0,
            'shipping' => 0
        ];
    }

    /**
     * @param OrderModel $order
     * @param DataObject $data
     */
    private function createInvoicedCheckoutData(OrderModel $order, DataObject $data)
    {
        $items = [];
        $total = 0;
        $discount = 0;

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            $total+= $this->_checkoutHelper->getInvoiceTotal($invoice);

            /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
            foreach ($invoice->getAllItems() as $item) {
                if (!($product = $this->_checkoutHelper->fetchProduct($item->getProductId()))) {
                    continue;
                }

                $quantity = $item->getQty();

                // its a kind of duplication
                if (!$subtotal = $this->_checkoutHelper->getProductTotalInvoiceItem($item) * $quantity) {
                    continue;
                }

                $subtotal = round($subtotal, 2);
                $baseDiscount = $item->getDiscountAmount() ?: 0;
                $discount += $baseDiscount;

                $items[] = array_merge(
                    $this->_checkoutHelper->getCustomAttributes($product),
                    [
                        'product_id' => $product->getId(),
                        'product_name' => $product->getName(),
                        'product_url' => $product->getUrlInStore(),
                        'price' => $subtotal / $quantity,
                        'discount' => $baseDiscount,
                        'subtotal' => $subtotal - $baseDiscount,
                        'sku' => $item->getSku(), // $product->getSku() throws sometimes exception
                        'quantity' => $quantity,
                        'product_category' => implode(
                            ', ',
                            $this->_checkoutHelper->getProductCategories($product)
                        ),
                    ]
                );
            }
        }

        $data->addData(
            [
                'total' => $total,
                'items' => $items,
                'discount' => $discount,
                'shipping' => $order->getShippingAmount() ?: 0
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->_scopeConfig->getValue(ConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        $checkoutSendingType = $this->_checkoutHelper->getCheckoutSendingType($order);
        $pointsBurned = 0;

        /** @var \Magento\Framework\DataObject $data */
        $data = $observer->getData('event_data');

        if ($this->_checkoutHelper->getPointMechanismType() == PointMechanismType::USING_REWARDS) {
            $orderData = $order->getData();

            if (isset($orderData['reward_points_balance'])) {
                $pointsBurned = $orderData['reward_points_balance'];
            }
        } else {
            $rule = $this->_checkoutHelper->getCouponRule($order->getCouponCode());

            if ($rule) {
                $pointsBurned = $this
                    ->_dataHelper
                    ->getPriceAfterConversion(
                        $this->_checkoutHelper->calculatePointsBurned($rule->getDiscountAmount()),
                        $order->getStore()->getId()
                    );
            }
        }

        // Adding the default checkout data to the payload object
        $data->addData($this->createDefaultCheckoutData($order));

        // Adding the points_burned attribute to the payload object
        $data->addData(['points_burned' => $pointsBurned]);

        // If the channel cookie is set, adding that to the payload object
        if ($channel = $this->_checkoutHelper->getChannelCookie()) {
            $data->setData('channel', $channel);
        }

        if (CheckoutSendingType::TYPE_PURCHASE_COMPLETED == $checkoutSendingType) {
            // shipped and invoice checkout type is set
            $this->createInvoicedCheckoutData($order, $data);
        } else {
            // explicitly after the payment checkout event will be written
            // so it don't have to be shipped or invoiced
            $this->createPurchasedCheckoutData($order, $data);
        }
    }

    /**
     * Collecting data from order instead of invoice because invoice doesn't exist yet
     *
     * Apparently it's a Beerhawk customization
     *
     * @param OrderModel $order
     * @param DataObject $data
     */
    public function createPurchasedCheckoutData(OrderModel $order, DataObject $data)
    {
        $items = [];
        $discount = 0;

        foreach ($order->getAllItems() as $item) {
            if (!$product = $item->getProduct()) {
                continue;
            }

            $quantity = $item->getQtyOrdered();

            // its a kind of duplication
            if (!$subtotal = $this->_checkoutHelper->getProductTotalOrderItem($item) * $quantity) {
                continue;
            }

            $subtotal = round($subtotal, 2);
            $baseDiscount = $item->getDiscountAmount() ?: 0;
            $discount += $baseDiscount;

            $items[] = array_merge(
                $this->_checkoutHelper->getCustomAttributes($product),
                [
                    'product_id' => $product->getId(),
                    'product_name' => $product->getName(),
                    'product_url' => $product->getUrlInStore(),
                    'price' => $subtotal / $quantity,
                    'discount' => $baseDiscount,
                    'subtotal' => $subtotal - $baseDiscount,
                    'sku' => $item->getSku(), // $product->getSku() throws sometimes exception
                    'quantity' => $quantity,
                    'product_category' => implode(
                        ', ',
                        $this->_checkoutHelper->getProductCategories($product)
                    ),
                ]
            );
        }

        $data->addData(
            [
                'total' => $this->_checkoutHelper->getOrderTotal($order),
                'items' => $items,
                'discount' => $discount,
                'shipping' => $order->getShippingAmount() ?: 0
            ]
        );
    }
}
