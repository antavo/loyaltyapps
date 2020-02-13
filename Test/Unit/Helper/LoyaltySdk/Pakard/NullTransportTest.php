<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\LoyaltySdk\Pakard;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\RestClient;

/**
 * Tests for {@see Pakard\RestClient\NullTransport} class.
 */
class NullTransportTest extends \Antavo\LoyaltyApps\Test\Unit\TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\NullTransport::class;
    }

    /**
     * Tests sending with <tt>NullTransport</tt>: it should not throw any
     * exceptions and return with a <tt>NULL</tt> body.
     */
    public function testSend() {
        $this->assertNull(
            (new RestClient)
                ->setTransport(new \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\NullTransport)
                ->send(
                    \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\RequestInterface::METHOD_POST,
                    'schema://example.com'
                )
        );
    }
}
