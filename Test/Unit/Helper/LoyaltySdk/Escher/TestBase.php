<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\LoyaltySdk\Escher;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\EmarTech\Escher\Escher;
use \DateTime;
use \DateTimeZone;

abstract class TestBase extends \PHPUnit\Framework\TestCase
{
    protected function assertEqualMaps(array $expected, array $actual, $message = '')
    {
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * @param string $credentialScope
     * @param DateTime $date
     * @return Escher
     */
    protected function createEscher($credentialScope = 'us-east-1/host/aws4_request', $date = null)
    {
        if (is_null($date))
        {
            $date = $this->getDate();
        }
        return Escher::create($credentialScope, $date)
            ->setAlgoPrefix('EMS')->setVendorKey('EMS')->setAuthHeaderKey('X-Ems-Auth')->setDateHeaderKey('X-Ems-Date');
    }

    /**
     * @return DateTime
     */
    protected function getDate()
    {
        return new DateTime('2011/05/11 12:00:00', new DateTimeZone("UTC"));
    }
}
