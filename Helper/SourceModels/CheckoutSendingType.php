<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 */
class CheckoutSendingType implements ArrayInterface
{
    /**
     * @var string
     */
    const TYPE_PAYMENT_RECEIVED = 'payment_received';

    /**
     * @var string
     */
    const TYPE_PURCHASE_COMPLETED = 'purchase_completed';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Payment received',
                'value' => self::TYPE_PAYMENT_RECEIVED,
            ],
            [
                'label' => 'Purchase completed',
                'value' => self::TYPE_PURCHASE_COMPLETED,
            ],
        ];
    }
}
