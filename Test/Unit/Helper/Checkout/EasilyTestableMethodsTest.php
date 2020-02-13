<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class EasilyTestableMethodsTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getChannelCookie()
     */
    public function testGetChannelCookie()
    {
        $value = '__atskuki';

        $this->_cookieManager
            ->expects($this->once())
            ->method('getCookie')
            ->willReturn($value);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'cookieManager' => $this->_cookieManager
            ]
        );

        $this->assertSame($value, $class->getChannelCookie());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::calculateCouponValue()
     */
    public function testCalculateCouponValue()
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

        $this->assertEquals(5, $class->calculateCouponValue(10));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getPointRate()
     */
    public function testGetPointRate()
    {
        $value = 0.5;

        $this->_scopeConfig
            ->method('getValue')
            ->willReturn($value);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );

        $this->assertEquals($value, $class->getPointRate());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::calculatePointsBurned()
     */
    public function testCalculatePointsBurned()
    {
        $this->_scopeConfig
            ->method('getValue')
            ->willReturn(0.6);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );

        $this->assertEquals(60, $class->calculateCouponValue(100));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getPointMechanismType()
     */
    public function testGetPointMechanismType()
    {
        $pointMechanism = 'using_rewards';

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($pointMechanism);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );

        $this->assertEquals($pointMechanism, $class->getPointMechanismType());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getCheckoutSendingType()
     */
    public function testGetCheckoutSendingType()
    {
        $checkoutSendingType = 'payment_received';
        $orderModel = $this->_orderModel;
        $orderModel
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($checkoutSendingType);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        /** @var \Magento\Sales\Model\Order  $orderModel */
        $this->assertEquals($checkoutSendingType, $class->getCheckoutSendingType($orderModel));
    }
}
