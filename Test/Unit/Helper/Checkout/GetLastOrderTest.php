<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class GetLastOrderTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getLastOrder()
     */
    public function testGetLastOrder_order_id_null()
    {
        $this->_orderModel
            ->expects($this->any())
            ->method('getId')
            ->willReturn(NULL);

        $this->_checkoutSession
            ->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($this->_orderModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'checkoutSession' => $this->_checkoutSession,
                'orderModel' => $this->_orderModel,
            ]
        );

        $this->assertNull($class->getLastOrder());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getLastOrder()
     */
    public function testGetLastOrder_getRealOrderId_null()
    {
        $this->_orderModel
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->_orderModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_orderModel);

        $this->_checkoutSession
            ->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($this->_orderModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'checkoutSession' => $this->_checkoutSession,
                'orderModel' => $this->_orderModel,
            ]
        );

        $this->assertNull($class->getLastOrder());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getLastOrder()
     */
    public function testGetLastOrder()
    {
        $this->_orderModel
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->_orderModel
            ->expects($this->once())
            ->method('getRealOrderId')
            ->willReturn(2);

        $this->_orderModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_orderModel);

        $this->_checkoutSession
            ->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($this->_orderModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'checkoutSession' => $this->_checkoutSession,
                'orderModel' => $this->_orderModel,
            ]
        );

        $this->assertInstanceof(
            '\Magento\Sales\Model\Order',
            $class->getLastOrder()
        );
    }
}
