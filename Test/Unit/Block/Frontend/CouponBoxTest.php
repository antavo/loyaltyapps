<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend;

use Antavo\LoyaltyApps\Block\Frontend\CouponBox;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CouponBoxTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return CouponBox::class;
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
     * @param $enabled
     */
    public function isEnabled($enabled)
    {
        $scopeConfigInterface = $this
            ->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigInterface
            ->method('getValue')
            ->willReturn($enabled);

        $storeManager = $this
            ->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->getMock();

        $storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->setUpStoreModel());

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $scopeConfigInterface,
                'storeManager' => $storeManager,
            ]
        );

        if ($enabled) {
            $this->assertTrue($block->isEnabled());
        } else {
            $this->assertFalse($block->isEnabled());
        }
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::isEnabled()
     */
    public function testIsEnabled()
    {
        $this->isEnabled(TRUE);
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::isEnabled()
     */
    public function testIsEnabled_disabled()
    {
        $this->isEnabled(FALSE);
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::getPointsBurned()
     */
    public function testGetPointsBurned()
    {
        $value = 20;

        $cartHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $cartHelper
            ->expects($this->once())
            ->method('getPointsBurned')
            ->willReturn($value);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block */
        $block = $this->getClassMock(
            [
                'cartHelper' => $cartHelper
            ]
        );

        $this->assertSame($value, $block->getPointsBurned());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::getPointRedeemLimit()
     */
    public function testGetPointRedeemLimit()
    {
        $limit = 0;

        $cartHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $cartHelper
            ->expects($this->once())
            ->method('getPointRedeemLimit')
            ->willReturn($limit);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block */
        $block = $this->getClassMock(
            [
                'cartHelper' => $cartHelper
            ]
        );

        $this->assertSame($limit, $block->getPointRedeemLimit());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::getCurrentStoreCurrencySymbol()
     */
    public function testGetCurrentStoreCurrencySymbol()
    {
        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $this->setUpStoreManager($storeModel)
            ]
        );

        $this->assertSame('$', $block->getCurrentStoreCurrencySymbol());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::getStoreUrl()
     */
    public function testGetStoreUrl()
    {
        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->setUpStoreModel();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block */
        $block = $this->getClassMock(
            [
                'storeManager' => $this->setUpStoreManager($storeModel)
            ]
        );

        $this->assertSame('/test', $block->getStoreUrl('route'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::calculateCouponValue()
     */
    public function testCalculateCouponValue()
    {
        $cartHelper = $this
            ->getMockBuilder('Antavo\LoyaltyApps\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block*/
        $block = $this->getClassMock(
            [
                'cartHelper' => $cartHelper,
            ]
        );

        $cartHelper
            ->expects($this->any())
            ->method('calculateCouponValue')
            ->will($this->returnValue(200));

        $this->assertEquals(200, $block->calculateCouponValue(10));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::getCustomerSpendablePoints()
     */
    public function testGetCustomerSpendablePoints_positive()
    {
        $customerHelper = $this
            ->getMockBuilder('Antavo\LoyaltyApps\Helper\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block*/
        $block = $this->getClassMock(
            [
                'customerHelper' => $customerHelper,
            ]
        );

        $customerHelper
            ->expects($this->any())
            ->method('getSpendablePoints')
            ->will($this->returnValue(200));

        $this->assertEquals(200, $block->getCustomerSpendablePoints());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\CouponBox::getCustomerSpendablePoints()
     */
    public function testGetCustomerSpendablePoints_negative()
    {
        $customerHelper = $this
            ->getMockBuilder('Antavo\LoyaltyApps\Helper\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Antavo\LoyaltyApps\Block\Frontend\CouponBox $block*/
        $block = $this->getClassMock(
            [
                'customerHelper' => $customerHelper,
            ]
        );

        $customerHelper
            ->expects($this->any())
            ->method('getSpendablePoints')
            ->will($this->returnValue(-200));

        $this->assertEquals(0, $block->getCustomerSpendablePoints());
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpStoreModel()
    {
        $storeModel = $this
            ->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeModel
            ->expects($this->any())
            ->method('getUrl')
            ->willReturn('/test');

        /** @var \Magento\Directory\Model\Currency $currencyModel */
        $currencyModel = $this->setUpCurrencyModel();

        $storeModel
            ->expects($this->any())
            ->method('getCurrentCurrency')
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

        $currencyModel
            ->expects($this->any())
            ->method('getCurrencySymbol')
            ->willReturn('$');

        return $currencyModel;
    }
}
