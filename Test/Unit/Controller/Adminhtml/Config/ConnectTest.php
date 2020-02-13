<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Adminhtml\Config;

use Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class StatusTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_storeModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_apiClient;

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
        return Connect::class;
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->_scopeConfig = $this
            ->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_httpRequest = $this
            ->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_storeModel = $this
            ->getMockBuilder(\Magento\Store\Model\Store::class)
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
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect::getPointMechanism()
     */
    public function testGetPointMechanism()
    {
        $value = 'using_coupons';

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        $this->assertSame($value, $class->getPointMechanism());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect::sendPluginSettings()
     */
    public function testSendPluginSettings_is_invoked()
    {
        $storeModel = $this->_storeModel;

        $this->_apiClient
            ->expects($this->once())
            ->method('post');

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect $class */
        $class = $this->getClassMock(
            [
                'apiClient' => $this->_apiClient,
            ]
        );

        /** @var \Magento\Store\Model\Store $storeModel */
        $class->sendPluginSettings($storeModel);
    }

    /**
     * Mostly invoke tests added
     *
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect::execute()
     */
    public function testExecute()
    {
        $this->_apiClient
            ->expects($this->once())
            ->method('getOAuthAccessToken');

        $this->_httpResponse
            ->expects($this->once())
            ->method('setRedirect')
            ->willReturn($this->_httpResponse);

        $this->_httpRequest
            ->expects($this->once())
            ->method('getQuery');

        $this->_httpRequest
            ->expects($this->once())
            ->method('getRequestUri');

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Config\Connect $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'apiClient' => $this->_apiClient,
            ]
        );

        $class->execute();
    }
}
