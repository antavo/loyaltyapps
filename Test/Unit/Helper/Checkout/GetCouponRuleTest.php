<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class GetCouponRuleTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getCouponRule()
     */
    public function testGetCouponRule_noId()
    {
        $this->_couponModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(NULL);

        $this->_couponModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_couponModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'couponModel' => $this->_couponModel,
            ]
        );
        $this->assertNull($class->getCouponRule('TEST'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getCouponRule()
     */
    public function testGetCouponRule_noRuleId()
    {
        $this->_couponModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(10);

        $this->_couponModel
            ->expects($this->once())
            ->method('getRuleId')
            ->willReturn(NULL);

        $this->_couponModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_couponModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'couponModel' => $this->_couponModel,
            ]
        );
        $this->assertNull($class->getCouponRule('TEST'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getCouponRule()
     */
    public function testGetCouponRule_invalidCouponCode()
    {
        $this->_couponModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(10);

        $this->_couponModel
            ->expects($this->once())
            ->method('getRuleId')
            ->willReturn('100');

        $this->_couponModel
            ->expects($this->once())
            ->method('getCode')
            ->willReturn('INVALID_CODE');

        $this->_couponModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_couponModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'couponModel' => $this->_couponModel,
            ]
        );
        $this->assertNull($class->getCouponRule('TEST'));
    }
}
