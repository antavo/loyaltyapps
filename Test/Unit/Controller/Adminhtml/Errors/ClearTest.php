<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Adminhtml\Errors;

use Antavo\LoyaltyApps\Controller\Adminhtml\Errors\Clear;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ClearTest extends TestCase
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
     * @inheritdoc
     */
    protected function getClass()
    {
        return Clear::class;
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
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Errors\Clear::execute()
     */
    public function testExecute()
    {
        $this->_httpResponse
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn($this->_httpResponse);

        $this->_httpResponse
            ->expects($this->atLeastOnce())
            ->method('sendHeaders')
            ->willReturn($this->_httpResponse);

        $this->_httpRequest
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(FALSE);

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Errors\Clear $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
            ]
        );

        $class->execute();
    }
}
