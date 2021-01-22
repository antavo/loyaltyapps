<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend\IncentivizedPurchase;

use Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ProductPointsTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return ProductPoints::class;
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
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::createMockedTransactionFor()
     */
    public function testCreateMockedTransactionFor()
    {
        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        /** @var \Magento\Directory\Model\Currency $currencyModel */
        $currencyModel = $this->setUpCurrencyModel();

        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel($currencyModel);

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->setUpStoreManager($storeModel);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
                'checkoutHelper' => $this->setUpCheckoutHelper(),
            ]
        );

        $this->assertSame(
            [
                'total' => 33,
                'transaction_id' => 'random_tx_' . time(),
                'items' => [
                    [
                        'product_id' => 'T-0001',
                        'product_name' => 'heavy',
                        'product_url' => 'https://google.com/magento',
                        'quantity' => 1,
                        'subtotal' => 33,
                        'sku' => 'T-0001',
                        'price' => 33,
                        'discount' => 0,
                        'product_category' => 'tool'
                    ]
                ]
            ],
            $block->createMockedTransactionFor($productModel)
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::calculateProductPrice()
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

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
            ]
        );

        $this->assertEquals(33, $block->calculateProductPrice($productModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::calculateProductPrice()
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

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
                'scopeConfig' => $scopeConfigInterface,
            ]
        );

        $this->assertEquals(33, $block->calculateProductPrice($productModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::calculateProductPrice()
     */
    public function testCalculateProductPrice_conversationEnabled_diff_currency()
    {
        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        $currencyModel = $this->setUpCurrencyModel();

        $currencyModel
            ->expects($this->once())
            ->method('getRate')
            ->willReturn(9.2);

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

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $storeManager,
                'scopeConfig' => $scopeConfigInterface,
                'currencyModel' => $currencyModel,
            ]
        );

        $this->assertEquals(
            303.6,
            $block->calculateProductPrice($productModel)
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::exportProductProperties()
     */
    public function testExportProductProperties()
    {
        /** @var \Magento\Directory\Model\Currency $currencyModel */
        $currencyModel = $this->setUpCurrencyModel();

        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel($currencyModel);

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->setUpStoreManager($storeModel);

        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'checkoutHelper' => $this->setUpCheckoutHelper(),
                'storeManager' => $storeManager,
            ]
        );

        $this->assertSame(
            [
                'product_id' => 'T-0001',
                'product_name' => 'heavy',
                'product_url' => 'https://google.com/magento',
                'quantity' => 1,
                'subtotal' => 33,
                'sku' => 'T-0001',
                'price' => 33,
                'discount' => 0,
                'product_category' => 'tool'
            ],
            $block->exportProductProperties($productModel)
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::exportProductProperties()
     */
    public function testExportProductProperties_currency_change()
    {
        $scopeConfigInterface = $this
            ->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigInterface
            ->method('getValue')
            ->willReturn(TRUE);

        $currencyModel = $this->setUpCurrencyModel();

        $currencyModel
            ->expects($this->once())
            ->method('getRate')
            ->willReturn(10);

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

        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $this->setUpProductModel();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'checkoutHelper' => $this->setUpCheckoutHelper(),
                'storeManager' => $storeManager,
                'scopeConfig' => $scopeConfigInterface,
                'currencyModel' => $currencyModel,
            ]
        );

        $this->assertSame(
            [
                'product_id' => 'T-0001',
                'product_name' => 'heavy',
                'product_url' => 'https://google.com/magento',
                'quantity' => 1,
                'subtotal' => 330,
                'sku' => 'T-0001',
                'price' => 330,
                'discount' => 0,
                'product_category' => 'tool'
            ],
            $block->exportProductProperties($productModel)
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::fetchProduct()
     */
    public function testFetchProduct_product_not_exist()
    {
        $productModel = $this->setUpProductModel();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'productModel' => $productModel
            ]
        );

        $this->assertNull($block->fetchProduct(1));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::fetchProduct()
     */
    public function testFetchProduct()
    {
        $productModel = $this->setUpProductModel();
        $productId = 1;

        $productModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'productModel' => $productModel
            ]
        );

        $this->assertInstanceOf(
            '\Magento\Catalog\Model\Product',
            $block->fetchProduct($productId)
        );
    }

    /**
     * @param bool $enabled
     */
    public function isEnabled($enabled)
    {
        $incentivizedPurchaseHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\App\IncentivizedPurchase')
            ->disableOriginalConstructor()
            ->getMock();

        $incentivizedPurchaseHelper
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn($enabled);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints $block */
        $block = $this->getClassMock(
            [
                'incentivizedPurchaseHelper' => $incentivizedPurchaseHelper,
            ]
        );

        if ($enabled) {
            $this->assertTrue($block->isEnabled());
        } else {
            $this->assertFalse($block->isEnabled());
        }
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::isEnabled()
     */
    public function testIsEnabled()
    {
        $this->isEnabled(TRUE);
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase\ProductPoints::isEnabled()
     */
    public function testIsEnabled_disabled()
    {
        $this->isEnabled(FALSE);
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
            ->willReturn(['tool']);

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
            ->willReturn('T-0001');

        $productModel
            ->method('getName')
            ->willReturn('heavy');

        $productModel
            ->method('getFinalPrice')
            ->willReturn(33);

        $productModel
            ->method('getUrlInStore')
            ->willReturn('https://google.com/magento');

        $productModel
            ->method('getUrlInStore')
            ->willReturn('https://something-url.com');

        $productModel
            ->method('load')
            ->willReturn($productModel);

        return $productModel;
    }
}
