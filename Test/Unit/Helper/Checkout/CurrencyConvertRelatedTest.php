<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Checkout;

use Antavo\LoyaltyApps\Test\Unit\Helper\CheckoutBase;

/**
 *
 */
class CurrencyConvertRelatedTest extends CheckoutBase
{
    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::isBaseCurrencyConvertEnabled()
     */
    public function testIsBaseCurrencyConvertEnabled_disabled()
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

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

        $this->assertFalse($class->isBaseCurrencyConvertEnabled());
    }


    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getOrderCurrency()
     */
    public function testGetOrderCurrency_conversion_disabled()
    {
        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        $currencyCode = 971;

        $orderModel = $this->_orderModel;
        $orderModel
            ->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn($currencyCode);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'storeManager' => $this->_storeManager,
            ]
        );

        // testing getOrderCurrencyCode hook returned value here
        /** @var $orderModel \Magento\Sales\Model\Order */
        $this->assertEquals($currencyCode, $class->getOrderCurrency($orderModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getOrderCurrency()
     */
    public function testGetOrderCurrency_conversion_enabled()
    {
        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(TRUE);

        $currencyCode = 555;

        $orderModel = $this->_orderModel;
        $orderModel
            ->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn($currencyCode);

        /** @var \Antavo\LoyaltyApps\Helper\Checkout $class */
        $class = $this->getClassMock(
            [
                'storeManager' => $this->_storeManager,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        // testing getBaseCurrencyCode hook returned value here
        /** @var $orderModel \Magento\Sales\Model\Order */
        $this->assertEquals($currencyCode, $class->getOrderCurrency($orderModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getInvoiceTotal()
     */
    public function testGetInvoiceTotal_enabled_conversion()
    {
        $value = 987.22;
        $invoiceModel = $this->_invoiceModel;
        $invoiceModel
            ->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($value);

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

        /** @var \Magento\Sales\Model\Order\Invoice $invoiceModel */
        $this->assertSame($value, $class->getInvoiceTotal($invoiceModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getInvoiceTotal()
     */
    public function testGetInvoiceTotal_disabled_conversion()
    {
        $value = 671.42;
        $invoiceModel = $this->_invoiceModel;
        $invoiceModel
            ->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($value);

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

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

        /** @var \Magento\Sales\Model\Order\Invoice $invoiceModel */
        $this->assertSame($value, $class->getInvoiceTotal($invoiceModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getOrderTotal()
     */
    public function testGetOrderTotal_enabled_conversion()
    {
        $value = 555.44;
        $orderModel = $this->_orderModel;
        $orderModel
            ->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($value);

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

        /** @var \Magento\Sales\Model\Order $orderModel */
        $this->assertSame($value, $class->getOrderTotal($orderModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getOrderTotal()
     */
    public function testGetOrderTotal_disabled_conversion()
    {
        $value = 123.1;
        $orderModel = $this->_orderModel;
        $orderModel
            ->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($value);

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

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

        /** @var \Magento\Sales\Model\Order $orderModel */
        $this->assertSame($value, $class->getOrderTotal($orderModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getProductTotalInvoiceItem()
     */
    public function testGetProductTotalInvoiceItem_enabled_conversion()
    {
        $value = 33;
        $invoiceItemModel = $this->_invoiceItemModel;
        $invoiceItemModel
            ->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($value);

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

        /** @var \Magento\Sales\Model\Order\Invoice\Item $invoiceItemModel */
        $this->assertEquals($value, $class->getProductTotalInvoiceItem($invoiceItemModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getProductTotalInvoiceItem()
     */
    public function testGetProductTotalInvoiceItem_disabled_conversion()
    {
        $value = 33.33;
        $invoiceItemModel = $this->_invoiceItemModel;
        $invoiceItemModel
            ->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($value);

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

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

        /** @var \Magento\Sales\Model\Order\Invoice\Item $invoiceItemModel */
        $this->assertEquals($value, $class->getProductTotalInvoiceItem($invoiceItemModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getProductTotalOrderItem()
     */
    public function testGetProductTotalOrderItem_enabled_conversion()
    {
        $value = 75;
        $orderItemModel = $this->_orderItemModel;
        $orderItemModel
            ->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($value);

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

        /** @var \Magento\Sales\Model\Order\Item $orderItemModel */
        $this->assertEquals($value, $class->getProductTotalOrderItem($orderItemModel));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Checkout::getProductTotalOrderItem()
     */
    public function testGetProductTotalOrderItem_disabled_conversion()
    {
        $value = 61;
        $orderItemModel = $this->_orderItemModel;
        $orderItemModel
            ->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($value);

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

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

        /** @var \Magento\Sales\Model\Order\Item $orderItemModel */
        $this->assertEquals($value, $class->getProductTotalOrderItem($orderItemModel));
    }
}
