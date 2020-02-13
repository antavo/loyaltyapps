<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Token;

use Antavo\LoyaltyApps\Helper\Token\CustomerToken;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CustomerTokenTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return CustomerToken::class;
    }

    /**
     * @return array
     */
    public function propertyDefaultsProvider()
    {
        return [
            ['algorithm', 'sha256'],
            ['expires_at', 86400],
            ['data', NULL],
            ['secret', 'correct horse battery staple'],
        ];
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Antavo\LoyaltyApps\Helper\Token',
            new CustomerToken
        );
    }

    /**
     * @param string $property
     * @param mixed $expected
     * @dataProvider propertyDefaultsProvider
     * @coversNothing
     */
    public function testPropertyDefaults($property, $expected)
    {
        $this->assertEquals($expected, (new CustomerToken)->{$property});
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Token\CustomerToken::getCustomer()
     * @covers \Antavo\LoyaltyApps\Helper\Token\CustomerToken::setCustomer()
     */
    public function testGetSetCustomer()
    {
        $token = new CustomerToken;
        $this->assertNull($token->getCustomer());
        $token->setCustomer(['non valid array value']);
        $this->assertNull($token->getCustomer());
        $token->setCustomer('valid_customer');
        $this->assertEquals('valid_customer', $token->getCustomer());
    }
}
