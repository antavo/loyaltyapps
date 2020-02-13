<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Adminhtml\Checkout;

use Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class StatusTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_orderModel;

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
    private $_apiClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_phpEnvResponse;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_srcResponse;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_httpHeader;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Status::class;
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->_orderModel = $this
            ->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_httpRequest = $this
            ->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_apiClient = $this
            ->getMockBuilder(\Antavo\LoyaltyApps\Helper\ApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_httpResponse = $this
            ->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_phpEnvResponse = $this
            ->getMockBuilder(\Magento\Framework\HTTP\PhpEnvironment\Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_srcResponse = $this
            ->getMockBuilder(\Zend\Http\PhpEnvironment\Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_httpHeader = $this
            ->getMockBuilder(\Magento\Framework\HTTP\Header::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Backend\App\Action',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status::loadOrder()
     */
    public function testLoadOrder_default()
    {
        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status $class */
        $class = $this->getClassMock();

        $this->assertNull($class->loadOrder('1'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status::loadOrder()
     */
    public function testLoadOrder()
    {
        $orderId = '56';

        $this->_orderModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $this->_orderModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_orderModel);

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status $class */
        $class = $this->getClassMock(
            [
                'orderModel' => $this->_orderModel,
            ]
        );

        /** @var \Magento\Sales\Model\Order $order */
        $order = $class->loadOrder($orderId);

        $this->assertInstanceOf('\Magento\Sales\Model\Order', $order);
        $this->assertSame('56', $order->getId());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status::loadOrder()
     */
    public function testHandleOrderStatusUpdate_do_nothing()
    {
        $orderModel = $this->_orderModel;

        $orderModel
            ->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(2);

        $this->_apiClient
            ->expects($this->never())
            ->method('sendEvent');

        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnCallback(
                function ($key) {
                    $post = [
                        'action' => 'something'
                    ];

                    if (!$key) {
                        return $post;
                    }

                    if (array_key_exists($key, $post)) {
                        return $post[$key];
                    }

                    return NULL;
                }
        );

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status $class */
        $class = $this->getClassMock(
            [
                'orderModel' => $orderModel,
                'request' => $this->_httpRequest,
                'apiClient' => $this->_apiClient,
            ]
        );

        // if api client send event is invoked test will fail
        /** @var \Magento\Sales\Model\Order $orderModel */
        $class->handleOrderStatusUpdate($orderModel);
    }

    /**
     * @return array
     */
    public function actionProvider()
    {
        return [
            ['approve', 'reject']
        ];
    }

    /**
     * @param string $action
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status::loadOrder()
     * @dataProvider actionProvider()
     */
    public function testHandleOrderStatusUpdate_invoked($action)
    {
        $orderModel = $this->_orderModel;

        $orderModel
            ->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(2);

        $this->_apiClient
            ->expects($this->once())
            ->method('sendEvent');

        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnCallback(
                function ($key) use ($action) {
                    $post = [
                        'action' => $action
                    ];

                    if (!$key) {
                        return $post;
                    }

                    if (array_key_exists($key, $post)) {
                        return $post[$key];
                    }

                    return NULL;
                }
        );

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status $class */
        $class = $this->getClassMock(
            [
                'orderModel' => $orderModel,
                'request' => $this->_httpRequest,
                'apiClient' => $this->_apiClient,
            ]
        );

        // api client should be invoked once!
        /** @var \Magento\Sales\Model\Order $orderModel */
        $class->handleOrderStatusUpdate($orderModel);
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status::execute()
     */
    public function testExecute_isPost_false()
    {
        $this->_httpResponse
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn($this->_httpResponse);

        $this->_httpResponse
            ->expects($this->atLeastOnce())
            ->method('sendHeaders')
            ->willReturn($this->_phpEnvResponse);

        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(FALSE);

        // fails if order model tries to be loaded
        $this->_httpRequest
            ->expects($this->never())
            ->method('getPost');

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
            ]
        );

        $class->execute();
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status::execute()
     */
    public function testExecute_isPost_true()
    {
        $this->_httpResponse
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn($this->_httpResponse);

        $this->_httpResponse
            ->expects($this->atLeastOnce())
            ->method('sendHeaders')
            ->willReturn($this->_phpEnvResponse);

        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(TRUE);

        $this->_httpRequest
            ->expects($this->once())
            ->method('getPost');

        $this->_orderModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_orderModel);

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Checkout\Status $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'orderModel' => $this->_orderModel,
            ]
        );

        $class->execute();
    }
}
