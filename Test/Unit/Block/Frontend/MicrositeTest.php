<?php
namespace Antavo\LoyaltyApps\Test\Unit\Block\Frontend;

use Antavo\LoyaltyApps\Block\Frontend\Microsite;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class MicrositeTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Microsite::class;
    }
    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\View\Element\Template',
            $this->getClassMock()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\Microsite::getMicrositeUrl()
     */
    public function testGetMicrositeUrl_empty()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\Microsite $block */
        $block = $this->getClassMock();
        $this->assertNull($block->getMicrositeUrl());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Block\Frontend\Microsite::getMicrositeUrl()
     */
    public function testGetMicrositeUrl()
    {
        /** @var \Antavo\LoyaltyApps\Block\Frontend\Microsite $block */
        $block = $this->getClassMock();
        $this->assertNull($block->getMicrositeUrl());
    }
}
