<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Customer;

use Antavo\LoyaltyApps\Controller\Customer\Optin;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class IndexTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_storeModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_redirectInterface;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_customerModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cookieHelper;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Optin::class;
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

        $this->_storeManager = $this
            ->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_storeModel = $this
            ->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_context = $this
            ->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_redirectInterface = $this
            ->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->_customerSession = $this
            ->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_customerModel = $this
            ->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_cookieHelper = $this
            ->getMockBuilder(\Antavo\LoyaltyApps\Helper\Cookie::class)
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
     * @covers \Antavo\LoyaltyApps\Controller\Customer\Optin::getOptinRedirectUrl()
     */
    public function testGetOptinRedirectUrl()
    {
        $redirectUrl = 'https://smash.com';

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($redirectUrl);

        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        /** @var \Antavo\LoyaltyApps\Controller\Customer\Optin $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'storeManager' => $this->_storeManager,
            ]
        );

        $this->assertSame($redirectUrl, $class->getOptinRedirectUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Customer\Optin::getRedirectUrl()
     */
    public function testGetRedirectUrl()
    {
        $redirectUrl = 'https://smash.com';

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($redirectUrl);

        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        /** @var \Antavo\LoyaltyApps\Controller\Customer\Optin $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'storeManager' => $this->_storeManager,
            ]
        );

        $this->assertSame($redirectUrl, $class->getRedirectUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Customer\Optin::getRedirectUrl()
     */
    public function testGetRedirectUrl_no_config()
    {
        $url = 'http://referrer.com';

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

        $this->_redirectInterface
            ->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($url);

        $this->_context
            ->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->_redirectInterface);

        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        /** @var \Antavo\LoyaltyApps\Controller\Customer\Optin $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'storeManager' => $this->_storeManager,
                'context' => $this->_context,
            ]
        );

        $this->assertSame($url, $class->getRedirectUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Customer\Optin::execute()
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

        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        // code should reach this state
        $this->_customerSession
            ->expects($this->never())
            ->method('getCustomer');

        /** @var \Antavo\LoyaltyApps\Controller\Customer\Optin $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'storeManager' => $this->_storeManager,
                'customerSession' => $this->_customerSession,
            ]
        );

        $class->execute();
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Customer\Optin::execute()
     */
    public function testExecute_isPost_customer_id()
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

        $this->_storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->_storeModel);

        $this->_customerModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(50);

        $this->_cookieHelper
            ->expects($this->never())
            ->method('set');

        // code should reach this state
        $this->_customerSession
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->_customerModel);

        /** @var \Antavo\LoyaltyApps\Controller\Customer\Optin $class */
        $class = $this->getClassMock(
            [
                'request' => $this->_httpRequest,
                'response' => $this->_httpResponse,
                'storeManager' => $this->_storeManager,
                'customerSession' => $this->_customerSession,
                'cookieHelper' => $this->_cookieHelper,
            ]
        );

        $class->execute();
    }
}
