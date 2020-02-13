<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class GetProductCategoriesTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getProductCategories()
     */
    public function testGetProductCategories()
    {
        $productModel = $this->_productModel;
        $productModel
            ->expects($this->any())
            ->method('getCategoryIds')
            ->willReturn([1]);

        $this->_categoryModel
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->_categoryModel);

        $this->_categoryModel
            ->expects($this->any())
            ->method('getName')
            ->willReturn('getName');

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'categoryModel' => $this->_categoryModel,
            ]
        );

        /** @var \Magento\Catalog\Model\Product $productModel */
        $this->assertContains(
            'getName',
            $class->getProductCategories($productModel)[0]
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getProductCategories()
     */
    public function testGetProductCategories_not_found_category()
    {
        $productModel = $this->_productModel;
        $productModel
            ->expects($this->any())
            ->method('getCategoryIds')
            ->willReturn([1]);

        $this->_categoryModel
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->_categoryModel);

        $this->_categoryModel
            ->expects($this->any())
            ->method('getName')
            ->willReturn(NULL);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'categoryModel' => $this->_categoryModel,
            ]
        );

        /** @var \Magento\Catalog\Model\Product $productModel */
        $this->assertEmpty($class->getProductCategories($productModel));
    }
}
