<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 */
class PointMechanismType implements ArrayInterface
{
    /**
     * @var string
     */
    const USING_COUPONS = 'using_coupons';

    /**
     * @var string
     */
    const USING_REWARDS = 'using_rewards';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $result[] = [
            'label' => 'Using coupons',
            'value' => self::USING_COUPONS,
        ];

        if (class_exists('\Magento\Reward\Model\Reward')) {
            $result[] = [
                'label' => 'Using rewards',
                'value' => self::USING_REWARDS,
            ];
        }

        return $result;
    }
}
