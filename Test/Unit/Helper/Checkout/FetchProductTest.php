<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class FetchProductTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::fetchProduct()
     */
    public function testFetchProduct_notFound()
    {
        $this->_productModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_productModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'productModel' => $this->_productModel,
            ]
        );
        $this->assertEquals(NULL, $class->fetchProduct(10));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::fetchProduct()
     */
    public function testFetchProduct_found()
    {
        $this->_productModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(10);

        $this->_productModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_productModel);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'productModel' => $this->_productModel,
            ]
        );
        $this->assertEquals($this->_productModel, $class->fetchProduct(10));
    }
}
