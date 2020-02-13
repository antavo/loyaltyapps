<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\LoyaltySdk\Pakard;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\TransportException;

/**
 *
 */
class TransportExceptionTest extends \Antavo\LoyaltyApps\Test\Unit\TestCase
{
    /**
     * @inheritdoc
     */
    public function getClass()
    {
        return TransportException::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance() {
        $this->assertInstanceOf(
            \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\Exception::class,
            new TransportException
        );
    }
}
