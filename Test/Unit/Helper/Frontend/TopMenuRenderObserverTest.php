<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Frontend;

use Antavo\LoyaltyApps\Helper\Frontend\TopMenuRenderObserver;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class TopMenuRenderObserverTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_urlHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_observer;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return TopMenuRenderObserver::class;
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->_customerSession = $this
            ->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_urlHelper = $this
            ->getMockBuilder(\Magento\Framework\Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_scopeConfig = $this
            ->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_observer = $this
            ->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     *
     */
    public function testCreateMenuEntry_disabledPlugin()
    {
        $this->_customerSession
            ->expects($this->never())
            ->method('isLoggedIn');

        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(FALSE);

        $this->_urlHelper
            ->expects($this->never())
            ->method('getUrl')
            ->willReturn('loyaltycentral/index/index');

        $this->_observer
            ->expects($this->never())
            ->method('getData');

        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'customerSession' => $this->_customerSession,
                'urlHelper' => $this->_urlHelper,
            ]
        );
        $class->execute($this->_observer);
    }

    /**
     *
     */
    public function testCreateMenuEntry_loggedIn()
    {
        $this->_scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->willReturn(TRUE);

        $this->_customerSession
            ->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(TRUE);

        $this->_urlHelper
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('loyaltycentral/index/index');

        $menu = $this
            ->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tree = $this
            ->getMockBuilder(\Magento\Framework\Data\Tree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menu
            ->expects($this->any())
            ->method('getTree')
            ->willReturn($tree);

        $this->_observer
            ->expects($this->once())
            ->method('getData')
            ->willReturn($menu);

        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'customerSession' => $this->_customerSession,
                'urlHelper' => $this->_urlHelper,
            ]
        );
        $class->execute($this->_observer);
    }
}
