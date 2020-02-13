<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\FriendReferral;

use Antavo\LoyaltyApps\Controller\Friendreferral\Coupon;
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
    private $_phpEnvResponse;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Coupon::class;
    }

    /**
     * @inheritdoc
     */
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

        $this->_phpEnvResponse = $this
            ->getMockBuilder(\Magento\Framework\HTTP\PhpEnvironment\Response::class)
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
     * @covers \Antavo\LoyaltyApps\Controller\Friendreferral\Coupon::execute()
     */
    public function testExecute_simple()
    {
        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(FALSE);

        // fails if order model tries to be loaded
        $this->_httpRequest
            ->expects($this->never())
            ->method('getPost');

        /** @var \Antavo\LoyaltyApps\Controller\Friendreferral\Coupon $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
            ]
        );

        $class->execute();
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Friendreferral\Coupon::execute()
     */
    public function testExecute_expected_response()
    {
        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('isPost')
            ->willReturn(TRUE);

        $this->_httpResponse
            ->expects($this->once())
            ->method('setHeader')
            ->willReturn($this->_httpResponse);

        $this->_httpResponse
            ->expects($this->once())
            ->method('setHttpResponseCode')
            ->willReturn($this->_httpResponse);

        $this->_httpRequest
            ->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnCallback(
                function ($key) {
                    $post = [
                        'dummy' => 'something'
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

        /** @var \Antavo\LoyaltyApps\Controller\Friendreferral\Coupon $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
            ]
        );

        $class->execute();
    }
}
