<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\SourceModels;

use Antavo\LoyaltyApps\Helper\SourceModels\ApiLogLevel;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ApiLogLevelTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return ApiLogLevel::class;
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
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\ApiLogLevel::toOptionArray()
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'value' => '',
                    'label' => 'None',
                ],
                [
                    'value' => 'errors',
                    'label' => 'Errors only',
                ],
                [
                    'value' => 'all',
                    'label' => 'All requests',
                ],
            ],
            (new ApiLogLevel)->toOptionArray()
        );
    }
}
