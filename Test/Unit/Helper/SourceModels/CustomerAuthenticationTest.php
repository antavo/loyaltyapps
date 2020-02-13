<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\SourceModels;

use Antavo\LoyaltyApps\Helper\SourceModels\CustomerAuthentication;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CustomerAuthenticationTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return CustomerAuthentication::class;
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
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\CustomerAuthentication::toOptionArray()
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
//                [
//                    'label' => CustomerAuthentication::AUTHENTICATION_COOKIE,
//                    'value' => CustomerAuthentication::AUTHENTICATION_COOKIE,
//                ],
                [
                    'label' => CustomerAuthentication::AUTHENTICATION_SOCIAL,
                    'value' => CustomerAuthentication::AUTHENTICATION_SOCIAL,
                ],
            ],
            (new CustomerAuthentication)->toOptionArray()
        );
    }
}
