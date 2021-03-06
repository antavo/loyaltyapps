<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\LoyaltySdk\Antavo;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CustomerTokenTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function getClass() {
        return CustomerToken::class;
    }

    /**
     * @return array
     */
    public function domainDataProvider() {
        return [
            ['api.rs1.antavo.com', 'antavo.com'],
            ['www.recoil.co.uk', 'recoil.co.uk'],
            ['www.ausregistry.com.au', 'ausregistry.com.au'],
        ];
    }

    /**
     * @param string $domain
     * @param string $expected
     * @dataProvider domainDataProvider()
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::calculateBaseDomain()
     */
    public function testCalculateDomain($domain, $expected) {
        $this->assertSame(
            $expected,
            CustomerToken::calculateBaseDomain($domain)
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::__construct()
     */
    public function testConstructor() {
        putenv('HTTP_HOST=www.example.domain.co.uk');
        $this->assertSame(
            'domain.co.uk',
            (new CustomerToken('secret'))->getCookieDomain()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::getCookieDomain()
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::setCookieDomain()
     */
    public function testGetSetCookieDomain() {
        // Default domain value tested already in testConstructor().
        $token = new CustomerToken('secret');

        // Checking setter return value.
        $this->assertSame($token, $token->setCookieDomain('antavo.com'));

        // Reading back previously set value.
        $this->assertSame('antavo.com', $token->getCookieDomain());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::getCookieName()
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::setCookieName()
     */
    public function testGetSetCookieName() {
        $token = new CustomerToken('secret');

        // Checking default value.
        $this->assertSame('__alc', $token->getCookieName());

        // Checking setter return value.
        $this->assertSame($token, $token->setCookieName('custCookie'));

        // Reading back previously set value.
        $this->assertSame('custCookie', $token->getCookieName());
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::getCustomer()
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\CustomerToken::setCustomer()
     */
    public function testGetSetCustomer() {
        $token = new CustomerToken('secret');

        // Checking default value.
        $this->assertNull($token->getCustomer());

        // Checking setter return value.
        $this->assertSame($token, $token->setCustomer('test_customer'));

        // Reading back previously set value.
        $this->assertSame('test_customer', $token->getCustomer());

        // Reading back value after encode/decode.
        $this->assertSame(
            'test_customer',
            (new CustomerToken('secret'))
                ->setToken((string) $token)
                ->getCustomer()
        );
    }
}
