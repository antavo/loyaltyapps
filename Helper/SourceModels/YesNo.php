<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 */
class YesNo implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Yes',
                'value' => 1,
            ],
            [
                'label' => 'No',
                'value' => 0,
            ],
        ];
    }
}
