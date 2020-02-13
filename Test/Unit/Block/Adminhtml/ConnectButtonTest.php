<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Adminhtml;

use Antavo\LoyaltyApps\Block\Adminhtml\ConnectButton;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ConnectButtonTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return ConnectButton::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Config\Block\System\Config\Form\Field',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\ConnectButton::getAuthorizationUrl()
     */
    public function testGetAuthorizationUrl()
    {
        $url = 'https://something-url-here.com';
        $apiClientHelper = $this
            ->getMockBuilder('\Antavo\LoyaltyApps\Helper\ApiClient')
            ->disableOriginalConstructor()
            ->getMock();

        $apiClientHelper
            ->expects($this->once())
            ->method('getOAuthAuthorizationUrl')
            ->willReturn($url);

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\ConnectButton $block */
        $block = $this->getClassMock(
            [
                'apiClient' => $apiClientHelper
            ]
        );

        $this->assertSame($url, $block->getAuthorizationUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\ConnectButton::getButtonBlock()
     */
    public function testGetButtonBlock()
    {
        $layoutHelper = $this
            ->getMockBuilder('\Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();

        // mocking a backend button and LayoutInterface::createBlock returns with it
        $layoutHelper
            ->expects($this->once())
            ->method('createBlock')
            ->willReturn($this
                ->getMockBuilder('\Magento\Backend\Block\Widget\Button')
                ->disableOriginalConstructor()
                ->getMock()
            );

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\ConnectButton $block */
        $block = $this->getClassMock(
            [
                'layout' => $layoutHelper
            ]
        );

        $this->assertNotEmpty($block->getButtonBlock());
    }
}
