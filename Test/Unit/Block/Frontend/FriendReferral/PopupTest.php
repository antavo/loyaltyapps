<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend\FriendReferral;

use Antavo\LoyaltyApps\Block\Frontend\FriendReferral\Popup;
use Antavo\LoyaltyApps\Test\Unit\TestCase;
use Antavo\LoyaltyApps\Helper\App\Coupons\FriendReferral as FriendReferralHelper;

/**
 *
 */
class PopupTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Popup::class;
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
     * @covers \Antavo\LoyaltyApps\Block\Frontend\FriendReferral\Popup::getFriendReferralUrl()
     */
    public function testGetFriendReferralUrl()
    {
        $scopeConfigHelper = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigHelper->method('getValue')
            ->willReturn('something');

        /** @var \Antavo\LoyaltyApps\Block\Frontend\FriendReferral\Popup $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $scopeConfigHelper
            ]
        );

        $this->assertSame('something/invite', $block->getFriendReferralUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\FriendReferral\Popup::hasFriendReferralChannel()
     */
    public function testHasFriendReferralChannel()
    {
        $friendReferralHelper = $this
            ->getMockBuilder(FriendReferralHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $friendReferralHelper
            ->expects($this->any())
            ->method('hasChannel')
            ->willReturn(TRUE);

        /** @var \Antavo\LoyaltyApps\Block\Frontend\FriendReferral\Popup $block */
        $block = $this->getClassMock(
            [
                'friendReferralHelper' => $friendReferralHelper
            ]
        );

        $this->assertTrue($block->hasFriendReferralChannel('test'));
    }
}
