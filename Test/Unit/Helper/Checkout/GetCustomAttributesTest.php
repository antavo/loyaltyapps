<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class GetCustomAttributesTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getCustomAttributes()
     */
    public function testGetCustomAttributes_empty()
    {
        $productModel = $this->_productModel;

        $productModel
            ->expects($this->any())
            ->method('getResource')
            ->willReturn($this->_productModel);

        /** @var \Magento\Catalog\Model\Product $productModel */
        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'productModel' => $productModel
            ]
        );

        $this->assertSame(
            [
                'style' => NULL,
                'country' => NULL,
                'brewery' => NULL,
                'abvrange' => NULL,
                'alcoholic_content' => NULL,
                'bottle_size' => NULL,
            ],
            $class->getCustomAttributes($productModel)
        );
    }
}
