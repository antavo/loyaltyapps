<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\LoyaltySdk\Pakard;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\CurlTransport;

/**
 * Tests for {@see Pakard\RestClient\CurlTransport} class.
 */
class CurlTransportTest extends \Antavo\LoyaltyApps\Test\Unit\TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return CurlTransport::class;
    }

    /**
     * Provides CURL options with values of different types.
     * @return array
     */
    public function optionDataProvider() {
        return [
            'int' => [CURLOPT_CONNECTTIMEOUT, 10],
            'array' => [CURLOPT_HEADERFUNCTION, [$this, 'noSuchMethod']],
            'null' => [CURLOPT_HEADERFUNCTION, NULL],
            'bool' => [CURLOPT_RETURNTRANSFER, TRUE],
            'string' => [CURLOPT_SSL_CIPHER_LIST, 'TLSv1'],
        ];
    }

    /**
     * Tests <tt>getOption()</tt> and <tt>setOption()</tt> methods together:
     * return values, transitivity, type-safety.
     *
     * @param int $option
     * @param mixed $value
     * @dataProvider optionDataProvider
     */
    public function testGetSetOption($option, $value) {
        $this->assertSame(
            $value,
            (new CurlTransport)
                ->setOption($option, $value)
                ->getOption($option)
        );
    }
}
