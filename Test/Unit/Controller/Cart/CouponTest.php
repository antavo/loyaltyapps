<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Cart;

use Antavo\LoyaltyApps\Controller\Cart\Coupon;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CouponTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_httpRequest;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_httpResponse;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cartModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cartHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_customerSession;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Coupon::class;
    }

    public function setUp()
    {
        parent::setUp();

        $this->_httpRequest = $this
            ->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_httpResponse = $this
            ->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_cartModel = $this
            ->getMockBuilder(\Magento\Checkout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_customerSession = $this
            ->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_cartHelper = $this
            ->getMockBuilder(\Antavo\LoyaltyApps\Helper\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\App\Action\Action',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Cart\Coupon::execute()
     */
    public function testExecute_isPost_false()
    {
        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(FALSE);

        // this method must never be fired when isPost is false
        $this->_cartModel
            ->expects($this->never())
            ->method('getCustomerSession');

        /** @var \Antavo\LoyaltyApps\Controller\Cart\Coupon $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'cartModel' => $this->_cartModel,
            ]
        );

        $class->execute();
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Cart\Coupon::execute()
     */
    public function testExecute_no_customer_id()
    {
        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(TRUE);

        $this->_httpResponse
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn($this->_httpResponse);

        $this->_httpResponse
            ->expects($this->atLeastOnce())
            ->method('sendHeaders')
            ->willReturn($this->_httpResponse);

        $this->_cartModel
            ->expects($this->once())
            ->method('getCustomerSession')
            ->willReturn($this->_customerSession);

        $this->_httpRequest
            ->expects($this->never())
            ->method('getPost');

        /** @var \Antavo\LoyaltyApps\Controller\Cart\Coupon $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'cartModel' => $this->_cartModel,
                'customerSession' => $this->_customerSession,
            ]
        );

        $class->execute();
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Cart\Coupon::execute()
     */
    public function testExecute()
    {
        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(TRUE);

        $this->_httpResponse
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn($this->_httpResponse);

        $this->_httpResponse
            ->expects($this->atLeastOnce())
            ->method('sendHeaders')
            ->willReturn($this->_httpResponse);

        $this->_customerSession
            ->expects($this->once())
            ->method('getId')
            ->willReturn(44);

        $this->_cartModel
            ->expects($this->once())
            ->method('getCustomerSession')
            ->willReturn($this->_customerSession);

        // test if cart helper hook handleCartCoupon was fired
        $this->_cartHelper
            ->expects($this->once())
            ->method('handleCartCoupon');

        /** @var \Antavo\LoyaltyApps\Controller\Cart\Coupon $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'cartModel' => $this->_cartModel,
                'customerSession' => $this->_customerSession,
                'cartHelper' => $this->_cartHelper
            ]
        );

        $class->execute();
    }
}
