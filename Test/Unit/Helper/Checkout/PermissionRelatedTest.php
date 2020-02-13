<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class PermissionRelatedTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::isCheckoutEventSendingEnabled()
     */
    public function testIsCheckoutEventSendingEnabled()
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(TRUE);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );

        $this->assertTrue($class->isCheckoutEventSendingEnabled(1));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::isBaseCurrencyConvertEnabled()
     */
    public function testIsBaseCurrencyConvertEnabled()
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(TRUE);

        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'storeManager' => $this->_storeManager,
            ]
        );

        $this->assertTrue($class->isBaseCurrencyConvertEnabled());
    }
}
