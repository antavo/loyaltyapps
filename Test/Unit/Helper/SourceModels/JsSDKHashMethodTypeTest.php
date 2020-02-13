<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\SourceModels;

use Antavo\LoyaltyApps\Helper\SourceModels\JsSDKHashMethodType;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class JsSDKHashMethodTypeTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return JsSDKHashMethodType::class;
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
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\JsSDKHashMethodType::toOptionArray()
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'label' => 'Hash',
                    'value' => JsSDKHashMethodType::METHOD_HASH,
                ],
                [
                    'label' => 'Query string',
                    'value' => JsSDKHashMethodType::METHOD_QUERYSTRING,
                ],
            ],
            (new JsSDKHashMethodType)->toOptionArray()
        );
    }
}
