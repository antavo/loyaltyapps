<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend;

use Antavo\LoyaltyApps\Block\Frontend\JsSdk;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class JsSdkTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return JsSdk::class;
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
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getSdkUrl()
     */
    public function testGetSdkUrl()
    {
        $configHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $abstractEnvironmentHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment')
            ->getMock();

        $url = 'https://api-apps.antavo.com';

        $abstractEnvironmentHelper
            ->expects($this->once())
            ->method('getSdkUrl')
            ->willReturn($url);

        $configHelper
            ->expects($this->once())
            ->method('getEnvironment')
            ->willReturn($abstractEnvironmentHelper);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'configHelper' => $configHelper
            ]
        );

        $this->assertEquals($url, $block->getSdkUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getApiKey()
     */
    public function testGetApiKey()
    {
        $apiKey = 'AAABBBCCCKKK1244_a';

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $this->setUpScopeConfigInterface($apiKey)
            ]
        );

        $this->assertEquals($apiKey, $block->getApiKey());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getAuthenticationMethod()
     */
    public function testGetAuthenticationMethod()
    {
        $authMethod = 'social';

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $this->setUpScopeConfigInterface($authMethod)
            ]
        );

        $this->assertEquals($authMethod, $block->getAuthenticationMethod());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getSdkHashingMethod()
     */
    public function testGetSdkHashingMethod()
    {
        $hash = 'AAAAbbbCC_124CCASFAA!!áé&#';

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $this->setUpScopeConfigInterface($hash)
            ]
        );

        $this->assertEquals($hash, $block->getSdkHashingMethod());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getSdkHashingMethod()
     */
    public function testGetSdkHashingMethod_empty()
    {
        $hash = NULL;

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $this->setUpScopeConfigInterface($hash)
            ]
        );

        $this->assertEquals($hash, $block->getSdkHashingMethod());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getCustomerId()
     */
    public function testGetCustomerId_social()
    {
        $this->assertNull($this->getCustomerId('social'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getCustomerId()
     */
    public function testGetCustomerId_cookie()
    {
        $customerId = '555';
        $this->assertSame($customerId, $this->getCustomerId('cookie', $customerId));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getCustomerId()
     */
    public function testGetCustomerId_cookie_empty_customerId()
    {
        $this->assertSame('', $this->getCustomerId('cookie'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::getSdkConfiguration()
     */
    public function testGetSdkConfiguration()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock();
        $this->assertSame(
            [
                'auth' => [
                    'method' => NULL,
                    'cookie' => FALSE,
                ],
                'video' => [
                    'enabled' => TRUE,
                ],
                'social' => [
                    'enabled' => TRUE,
                ],
                'notifications' => FALSE,
                'socialShare' => [
                    'enabled' => FALSE,
                ],
                'tracking' => [
                    'hashMethod' => NULL,
                ],
            ],
            $block->getSdkConfiguration()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::isEnabled()
     */
    public function testIsEnabled_checkout_page()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'request' => $this->setUpHttpRequest('/checkout'),
            ]
        );

        // checkout out page is not allowed to initialize js sdk
        $this->assertFalse($block->isEnabled());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::isEnabled()
     */
    public function testIsEnabled_checkout_cart_page()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'request' => $this->setUpHttpRequest('/checkout/cart'),
            ]
        );

        $this->assertTrue($block->isEnabled());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::isEnabled()
     */
    public function testIsEnabled_checkout_one_page_success()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'request' => $this->setUpHttpRequest('/checkout/onepage/success'),
            ]
        );

        $this->assertTrue($block->isEnabled());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::isEnabled()
     */
    public function testIsEnabled_testUrl()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'request' => $this->setUpHttpRequest('/profile'),
            ]
        );

        $this->assertTrue($block->isEnabled());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::isSocialShareEnabled()
     */
    public function testIsSocialShareEnabled_disabled()
    {
        $value = FALSE;

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $this->setUpScopeConfigInterface($value)
            ]
        );

        $this->assertFalse($block->isSocialShareEnabled());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\JsSdk::isSocialShareEnabled()
     */
    public function testIsSocialShareEnabled()
    {
        $value = TRUE;

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $this->setUpScopeConfigInterface($value)
            ]
        );

        $this->assertTrue($block->isSocialShareEnabled());
    }

    /**
     * @param string $returnRequestUri
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpHttpRequest($returnRequestUri)
    {
        $httpRequest = $this
            ->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $httpRequest
            ->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($returnRequestUri);

        return $httpRequest;
    }

    /**
    * @param string $value
    * @return \PHPUnit_Framework_MockObject_MockObject
    */
    private function setUpScopeConfigInterface($value)
    {
        $scopeConfigInterface = $this
            ->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigInterface
            ->method('getValue')
            ->willReturn($value);

        return $scopeConfigInterface;
    }

    /**
     * @param string $method social|cookie
     * @param null|int $customerId
     * @return string
     */
    private function getCustomerId($method, $customerId = NULL)
    {
        $scopeConfigInterface = $this
            ->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigInterface
            ->method('getValue')
            ->willReturn($method);

        $sessionModel = $this
            ->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $sessionModel
            ->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\JsSdk $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $scopeConfigInterface,
                'session' => $sessionModel,
            ]
        );

        return $block->getCustomerId();
    }
}
