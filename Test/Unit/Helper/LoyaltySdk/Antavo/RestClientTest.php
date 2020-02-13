<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\LoyaltySdk\Antavo;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\EmarTech\Escher\Escher;
use Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient;
use Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\RequestInterface;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class RestClientTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function getClass() {
        return RestClient::class;
    }
    /**
     * @coversNothing
     */
    public function testInheritance() {
        $this->assertInstanceOf(
            RestClient::class,
            new RestClient('st2', 'key', 'secret')
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient::__construct()
     */
    public function testConstructor() {
        $this->assertSame(
            'https://api.st2.antavo.com',
            (new RestClient('st2', 'key', 'secret'))->getBaseUrl()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient::getCredentialScope()
     */
    public function testGetCredentialScope() {
        $this->assertSame(
            'st2/api/antavo_request',
            (new RestClient('st2', 'key', 'secret'))
                ->getCredentialScope()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient::createEscher()
     */
    public function testCreateEscher() {
        $escher = (new RestClient('st2', 'key', 'secret'))->createEscher();
        $this->assertInstanceOf(Escher::class, $escher);
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient::sendEvent()
     */
    public function testSendEvent() {
        $client = $this->getMockBuilder(RestClient::class)
            ->setMethods(['send'])
            ->setConstructorArgs(['region', 'key', 'secret'])
            ->getMock();

        $client->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(RequestInterface::METHOD_POST),
                $this->equalTo('/events'),
                $this->equalTo(
                    [
                        'customer' => 'customer1',
                        'action' => 'opt_in',
                        'data' => [],
                    ]
                )
            );

        /** @var RestClient $client */
        $client->sendEvent('customer1', 'opt_in');
    }
}
