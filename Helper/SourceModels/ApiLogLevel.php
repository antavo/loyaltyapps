<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 */
class ApiLogLevel implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => 'None',
            ],
            [
                'value' => 'errors',
                'label' => 'Errors only',
            ],
            [
                'value' => 'all',
                'label' => 'All requests',
            ],
        ];
    }
}
