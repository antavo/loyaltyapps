<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\SourceModels;

use Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class PointMechanismTypeTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return PointMechanismType::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\Option\ArrayInterface',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType::toOptionArray()
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'label' => 'Using coupons',
                    'value' => PointMechanismType::USING_COUPONS,
                ],
            ],
            (new PointMechanismType)->toOptionArray()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\PointMechanismType::toOptionArray()
     */
    public function testToOptionArray_reward()
    {
        $this
            ->getMockBuilder('\Magento\Reward\Model\Reward')
            ->setMethods(
                [
                    'foo'
                ]
            )->getMock();

        $this->assertEquals(
            [
                [
                    'label' => 'Using coupons',
                    'value' => PointMechanismType::USING_COUPONS,
                ],
                [
                    'label' => 'Using rewards',
                    'value' => PointMechanismType::USING_REWARDS,
                ],
            ],
            (new PointMechanismType)->toOptionArray()
        );
    }
}
