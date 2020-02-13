<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Adminhtml\Errors;

use Antavo\LoyaltyApps\Controller\Adminhtml\Errors\Show;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ShowTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Show::class;
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
     * @covers \Antavo\LoyaltyApps\Controller\Adminhtml\Errors\Show::execute()
     */
    public function testExecute()
    {
        $pageFactory = $this
            ->getMockBuilder('\Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $resultPage = $this
            ->getMockBuilder('\Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $pageFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($resultPage);

        $pageConfig = $this
            ->getMockBuilder('\Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $pageTitle = $this
            ->getMockBuilder('\Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();

        $pageTitle
            ->expects($this->once())
            ->method('set')
            ->willReturn($pageTitle);

        $pageConfig
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);

        $resultPage
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfig);

        /** @var \Antavo\LoyaltyApps\Controller\Adminhtml\Errors\Show $class */
        $class = $this->getClassMock(
            [
                'pageFactory' => $pageFactory,
            ]
        );

        $this->assertInstanceOf(
            'Magento\Framework\View\Result\Page',
            $class->execute()
        );
    }
}
