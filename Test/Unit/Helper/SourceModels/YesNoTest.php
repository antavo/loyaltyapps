<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\SourceModels;

use Antavo\LoyaltyApps\Helper\SourceModels\YesNo;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class YesNoTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return YesNo::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\Option\ArrayInterface',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\YesNo::toOptionArray()
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'label' => 'Yes',
                    'value' => 1,
                ],
                [
                    'label' => 'No',
                    'value' => 0,
                ],
            ],
            (new YesNo)->toOptionArray()
        );
    }
}
