<?php
namespace Antavo\LoyaltyApps\Controller\Adminhtml\Checkout;

use Antavo\LoyaltyApps\Helper\ApiClient as AntavoApiClient;
use Antavo\LoyaltyApps\Controller\ControllerTrait;
use Magento\Backend\App\Action;
use Magento\Framework\HTTP\Header as HttpHeaderHelper;
use Magento\Sales\Model\Order as OrderModel;

/**
 *
 */
class Status extends Action
{
    use ControllerTrait;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_orderModel;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $_httpHeaderHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @param string $orderId
     * @return \Magento\Sales\Model\Order
     */
    public function loadOrder($orderId)
    {
        return $this->_orderModel->load($orderId);
    }

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Model\Order $orderModel
     * @param \Magento\Framework\HTTP\Header $httpHeaderHelper
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     */
    public function __construct(
        Action\Context $context,
        OrderModel $orderModel,
        HttpHeaderHelper $httpHeaderHelper,
        AntavoApiClient $apiClient
    ) {
        parent::__construct($context);
        $this->_orderModel = $orderModel;
        $this->_httpHeaderHelper = $httpHeaderHelper;
        $this->_apiClient = $apiClient;
    }

    /**
     * Handles checkout state changes: sends them in via Antavo Events API.
     *
     * @inheritdoc
     */
    public function execute()
    {
        try {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->getRequest();

            if ($request->isPost()) {
                $order = $this->loadOrder($request->getPost('checkout_id'));

                if ($order->getRealOrderId()) {
                    $this->handleOrderStatusUpdate($order);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } finally {
            // Redirecting to the referrer.
            /** @var \Magento\Framework\App\Response\Http $response */
            $response = $this->getResponse();
            $response
                ->setRedirect($this->_httpHeaderHelper->getHttpReferer())
                ->sendHeaders()
                ->send();
            return;
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function handleOrderStatusUpdate(OrderModel $order)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        try {
            switch ($request->getPost('action')) {
                case 'approve':
                    $this->_apiClient->sendEvent(
                        $order->getCustomerId(),
                        'checkout_accept',
                        ['transaction_id' => $order->getRealOrderId()]
                    );
                    break;
                case 'reject':
                    $this->_apiClient->sendEvent(
                        $order->getCustomerId(),
                        'checkout_reject',
                        ['transaction_id' => $order->getRealOrderId()]
                    );
                    break;
                default:
                    // Do nothing...
            }
        } catch (\Exception $e) {
            // Failing silently...
        }
    }
}
