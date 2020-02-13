<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Token;

use Antavo\LoyaltyApps\Helper\Token\InvalidTokenException;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class InvalidTokenExceptionTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return InvalidTokenException::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            '\Exception',
            new InvalidTokenException
        );
    }
}
