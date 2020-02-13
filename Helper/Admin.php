<?php
namespace Antavo\LoyaltyApps\Helper;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order as OrderModel;

/**
 *
 */
class Admin
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $_orderModel;

    /**
     * @param \Magento\Sales\Model\Order $orderModel
     */
    public function __construct(OrderModel $orderModel)
    {
        $this->_orderModel = $orderModel;
    }

    /**
     * This method fetches the order object from database by
     * the given order id.
     *
     * @param string $orderId
     * @return \Magento\Sales\Model\Order
     */
    public function loadOrder($orderId)
    {
        return $this->_orderModel->load($orderId);
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     */
    public function beforeGetOrderId(View $subject)
    {
        // Getting the order by the request "order_id" parameter
        $order = $this->loadOrder($subject->getRequest()->getParam('order_id'));

        // If there is no match for the order id, return
        if (!$order->getRealOrderId()) {
            return;
        }

        // Approve and reject buttons only need to be seen if
        // the order is already completed
        if (OrderModel::STATE_COMPLETE == $order->getState()) {
            // Adding approve button
            $subject->addButton(
                'approve_checkout',
                [
                    'label' => 'Approve',
                    'onclick' => 'document.getElementById(\'antavo_post_approve\').submit()',
                    'class' => 'reset'
                ],
                998
            );

            // Adding reject button.
            $subject->addButton(
                'reject_checkout',
                [
                    'label' => 'Reject',
                    'onclick' => 'document.getElementById(\'antavo_post_reject\').submit()',
                    'class' => 'reset'
                ],
                999
            );
        }
    }
}
