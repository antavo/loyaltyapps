<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend\IncentivizedPurchase;

use Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CartPointsTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return CartPoints::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\View\Element\Template',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints::getCartModel()
     */
    public function testGetCartModel()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints $block */
        $block = $this->getClassMock();
        $this->assertInstanceOf('\Magento\Checkout\Model\Cart', $block->getCartModel());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints::isEnabled()
     */
    public function testIsEnabled()
    {
        $incentivizedPurchaseHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\App\IncentivizedPurchase')
            ->disableOriginalConstructor()
            ->getMock();

        $incentivizedPurchaseHelper
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(TRUE);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints $block */
        $block = $this->getClassMock(
            [
                'incentivizedPurchaseHelper' => $incentivizedPurchaseHelper
            ]
        );

        $this->assertTrue($block->isEnabled());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints::calculateProductPrice()
     */
    public function testCalculateProductPrice_conversationDisabled()
    {
        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        /** @var \Magento\Directory\Model\Currency $currencyModel */
        $currencyModel = $this->setUpCurrencyModel();

        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel($currencyModel);

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->setUpStoreManager($storeModel);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
            ]
        );

        $this->assertEquals(55, $block->calculateProductPrice($productModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints::calculateProductPrice()
     */
    public function testCalculateProductPrice_conversationEnabled_same_currency()
    {
        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        /** @var \Magento\Directory\Model\Currency $currencyModel */
        $currencyModel = $this->setUpCurrencyModel();

        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel($currencyModel);

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->setUpStoreManager($storeModel);

        $scopeConfigInterface = $this
            ->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigInterface->method('getValue')
            ->willReturn(TRUE);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
                'scopeConfig' => $scopeConfigInterface,
            ]
        );

        $this->assertEquals(55, $block->calculateProductPrice($productModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints::calculateProductPrice()
     */
    public function testCalculateProductPrice_conversationEnabled_diff_currency()
    {
        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        $currencyModel = $this->setUpCurrencyModel();

        $currencyModel
            ->expects($this->once())
            ->method('getRate')
            ->willReturn(11.33);

        $currencyModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($currencyModel);

        /** @var \Magento\Directory\Model\Currency $currencyModel2 */
        $currencyModel2 = $this->setUpCurrencyModel2();

        /** @var \Magento\Directory\Model\Currency $currencyModel */
        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel($currencyModel, $currencyModel2);

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->setUpStoreManager($storeModel);

        $scopeConfigInterface = $this
            ->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigInterface->method('getValue')
            ->willReturn(TRUE);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
                'scopeConfig' => $scopeConfigInterface,
                'currencyModel' => $currencyModel,
            ]
        );

        $this->assertEquals(
            623.15,
            $block->calculateProductPrice($productModel)
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints::exportCartItemProperties()
     */
    public function testExportCartItemProperties()
    {
        /** @var \Magento\Directory\Model\Currency $currencyModel */
        $currencyModel = $this->setUpCurrencyModel();

        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel($currencyModel);

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->setUpStoreManager($storeModel);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\CartPoints $block */
        $block = $this->getClassMock(
            [
                'checkoutHelper' => $this->setUpCheckoutHelper(),
                'storeManager' => $storeManager,
            ]
        );

        /** @var \Magento\Quote\Model\Quote\Item $itemModel */
        $itemModel = $this->setUpItemModel();

        $this->assertSame(
            [
                'product_id' => 'SKU',
                'product_name' => 'beautiful',
                'product_url' => 'https://something-url.com',
                'discount' => 0,
                'price' => 55,
                'subtotal' => 110,
                'sku' => NULL,
                'quantity' => 2,
                'product_category' => 'category1, category2'
            ],
            $block->exportCartItemProperties($itemModel)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpCheckoutHelper()
    {
        $checkoutHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\Checkout')
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutHelper
            ->expects($this->any())
            ->method('getProductCategories')
            ->willReturn(
                [
                    'category1',
                    'category2'
                ]
            );

        return $checkoutHelper;
    }

    /**
     * @param \Magento\Store\Model\Store $storeModel
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpStoreManager(\Magento\Store\Model\Store $storeModel)
    {
        $storeManager = $this
            ->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->getMock();

        $storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($storeModel);

        return $storeManager;
    }

    /**
     * @param \Magento\Directory\Model\Currency $currencyModel
     * @param \Magento\Directory\Model\Currency|null $currencyModel2
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpStoreModel(
        \Magento\Directory\Model\Currency $currencyModel,
        \Magento\Directory\Model\Currency $currencyModel2 = NULL
    )
    {
        $storeModel = $this
            ->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeModel
            ->expects($this->any())
            ->method('getCurrentCurrency')
            ->willReturn($currencyModel2 ?: $currencyModel);

        $storeModel
            ->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn($currencyModel);

        return $storeModel;
    }

    /**
     * Default currency is USD
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpCurrencyModel()
    {
        $currencyModel = $this
            ->getMockBuilder('\Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->getMock();

        $currencyModel
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('USD');

        return $currencyModel;
    }

    /**
     * Default currency is EUR
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpCurrencyModel2()
    {
        $currencyModel = $this
            ->getMockBuilder('\Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->getMock();

        $currencyModel
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('EUR');

        return $currencyModel;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpProductModel()
    {
        $productModel = $this
            ->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productModel
            ->method('getSku')
            ->willReturn('SKU');

        $productModel
            ->method('getFinalPrice')
            ->willReturn(55);

        $productModel
            ->method('getUrlInStore')
            ->willReturn('https://something-url.com');

        return $productModel;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpItemModel()
    {
        $itemModel = $this
            ->getMockBuilder('\Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $itemModel
            ->expects($this->any())
            ->method('getName')
            ->willReturn('beautiful');

        $itemModel
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->setUpProductModel());

        $itemModel
            ->expects($this->any())
            ->method('getQty')
            ->willReturn(2);

        return $itemModel;
    }
}
