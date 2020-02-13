<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Adminhtml\Checkout;

use Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form;
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
        return Form::class;
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form::getFormKey()
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
            ->willReturn('alma');

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form $block */
        $block = $this->getClassMock(
            [
                'formKeyHelper' => $formKeyHelper
            ]
        );

        $this->assertSame('alma', $block->getFormKey());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form::getOrderId()
     */
    public function testGetOrderId()
    {
        $requestInterfaceHelper = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $requestInterfaceHelper->method('getParam')
            ->willReturn(10);

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form $block */
        $block = $this->getClassMock(
            [
                'request' => $requestInterfaceHelper
            ]
        );

        $this->assertSame(10, $block->getOrderId());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form::getActionUrl()
     */
    public function testGetActionUrl()
    {
        $dataHelper = $this
            ->getMockBuilder('\Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $dataHelper
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('somethingNiceUrl');

        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\Checkout\Form $block */
        $block = $this->getClassMock(
            [
                'dataHelper' => $dataHelper
            ]
        );

        $this->assertSame('somethingNiceUrl', $block->getActionUrl());
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
}
