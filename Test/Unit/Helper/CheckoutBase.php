<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper;

use Antavo\LoyaltyApps\Test\Unit\TestCase;
use Antavo\LoyaltyApps\Helper\Checkout;

/**
 *
 */
class CheckoutBase extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_categoryModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_couponModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cartHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_couponHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cookieManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_invoiceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_invoiceItemModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderItemModel;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Checkout::class;
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        // Mocking helper dependencies
        $this->_scopeConfig = $this
            ->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_checkoutSession = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_orderModel = $this
            ->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeManager = $this
            ->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeModel = $this
            ->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_ruleModel = $this
            ->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_categoryModel = $this
            ->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_couponModel = $this
            ->getMockBuilder(\Magento\SalesRule\Model\Coupon::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_cartHelper = $this
            ->getMockBuilder(\Antavo\LoyaltyApps\Helper\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_couponHelper = $this
            ->getMockBuilder(\Antavo\LoyaltyApps\Helper\Coupon::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_cookieManager = $this
            ->getMockBuilder(\Magento\Framework\Stdlib\CookieManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_productModel = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_invoiceModel = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_invoiceItemModel = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Invoice\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_orderItemModel = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
