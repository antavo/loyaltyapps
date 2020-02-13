<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend\Customer;

use Antavo\LoyaltyApps\Block\Frontend\Customer\OptInCheckbox;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class OptInCheckboxTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return OptInCheckbox::class;
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
     * @covers \Antavo\LoyaltyApps\Block\Frontend\Customer\OptInCheckbox::isEnabled()
     */
    public function testIsEnabled()
    {
        $scopeConfigHelper = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMock();

        $scopeConfigHelper->method('getValue')
            ->willReturn(TRUE);

        $storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->getMock();

        $storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this
                ->getMockBuilder('\Magento\Store\Api\Data\StoreInterface')
                ->getMock());

        /** @var \Antavo\LoyaltyApps\Block\Frontend\Customer\OptInCheckbox $block */
        $block = $this->getClassMock(
            [
                'scopeConfig' => $scopeConfigHelper,
                'storeManager' => $storeManager
            ]
        );

        $this->assertTrue($block->isEnabled());
    }
}
