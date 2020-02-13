<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper;

use Antavo\LoyaltyApps\Helper\Config;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ConfigTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Config::class;
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Config::getEnvironmentSelection()
     */
    public function testGetEnvironmentSelection()
    {
        /** @var \Antavo\LoyaltyApps\Helper\Config $class */
        $class = $this->getClassMock();

        $this->assertEquals(
            [
                $class::ENVIRONMENT_DEVELOPMENT,
                $class::ENVIRONMENT_TESTING,
                $class::ENVIRONMENT_RELEASE,
                $class::ENVIRONMENT_DEMO,
                $class::ENVIRONMENT_LOYALTY_STACK1,
                $class::ENVIRONMENT_LOYALTY_STACK2
            ],
            $class::getEnvironmentSelection()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Config::getEnvironment()
     * @covers \Antavo\LoyaltyApps\Helper\Config::setEnvironment()
     */
    public function testGetSetEnvironment()
    {
        /** @var \Antavo\LoyaltyApps\Helper\Config $class */
        $class = $this->getClassMock();

        $abstractEnvironmentHelper = $this
            ->getMockBuilder('Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment')
            ->getMock();

        $url = 'https://api-apps.antavo.com';

        $abstractEnvironmentHelper
            ->expects($this->once())
            ->method('getApiUrl')
            ->willReturn($url);

        /** @var \Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment $abstractEnvironmentHelper */
        $class->setEnvironment($abstractEnvironmentHelper);

        $this->assertInstanceof(
            'Antavo\LoyaltyApps\Helper\Environments\AbstractEnvironment',
            $class->getEnvironment()
        );

        // making sure the setter works properly
        $this->assertSame($url, $class->getEnvironment()->getApiUrl());
    }
}
