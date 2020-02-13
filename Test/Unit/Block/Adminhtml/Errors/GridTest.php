<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Adminhtml\Errors;

use Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class FormTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Grid::class;
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
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid::getClearActionUrl()
     */
    public function testGetClearActionUrl()
    {
        $dataHelper = $this
            ->getMockBuilder('\Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelper
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('something/url');

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid $block */
        $block = $this->getClassMock(
            [
                'dataHelper' => $dataHelper
            ]
        );

        $this->assertSame('something/url', $block->getClearActionUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid::getLogLevel()
     */
    public function testGetLogLevel()
    {
        $scopeConfigHelper = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigHelper->method('getValue')
            ->willReturn('Volt');

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $scopeConfigHelper
            ]
        );

        $this->assertSame('Volt', $block->getLogLevel());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid::getFormKey()
     */
    public function testGetFormKey()
    {
        $formKeyHelper = $this
            ->getMockBuilder('\Magento\Framework\Data\Form\FormKey')
            ->disableOriginalConstructor()
            ->getMock();

        $formKeyHelper
            ->expects($this->once())
            ->method('getFormKey')
            ->willReturn('sajt');

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\Errors\Grid $block */
        $block = $this->getClassMock(
            [
                'formKeyHelper' => $formKeyHelper
            ]
        );

        $this->assertSame('sajt', $block->getFormKey());
    }
}
