<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class FetchCouponRuleTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::fetchCouponRule()
     */
    public function testFetchCouponRule_notFound()
    {
        $this->_ruleModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_ruleModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'ruleModel' => $this->_ruleModel,
            ]
        );

        /** @var \Magento\SalesRule\Model\Coupon $couponModel */
        $couponModel = $this->_couponModel;

        $this->assertEquals(NULL, $class->fetchCouponRule($couponModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::fetchCouponRule()
     */
    public function testFetchCouponRule_found()
    {
        $this->_ruleModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(10);

        $this->_ruleModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_ruleModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'ruleModel' => $this->_ruleModel,
            ]
        );

        /** @var \Magento\SalesRule\Model\Coupon $couponModel */
        $couponModel = $this->_couponModel;

        $this->assertEquals($this->_ruleModel, $class->fetchCouponRule($couponModel));
    }
}
