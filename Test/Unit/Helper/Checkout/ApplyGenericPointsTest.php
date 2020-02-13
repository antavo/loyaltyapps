<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class ApplyGenericPointsTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::applyGenericPoints()
     */
    public function testApplyGenericPoints_empty()
    {
        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock();
        $value = ['dummy'];
        $this->assertSame($value, $class->applyGenericPoints($value));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::applyGenericPoints()
     */
    public function testApplyGenericPoints()
    {
        $this->_scopeConfig
            ->method('getValue')
            ->willReturn(0.5);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );

        $this->assertSame(
            [
                'rewarded_points' => 5.0,
                'total' => 30,
            ],
            $class->applyGenericPoints(
                [
                    'rewarded_points' => 10,
                    'total' => 30,
                ]
            )
        );
    }
}
