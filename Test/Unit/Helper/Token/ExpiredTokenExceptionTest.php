<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\Token;

use Antavo\LoyaltyApps\Helper\Token\ExpiredTokenException;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class ExpiredTokenExceptionTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return ExpiredTokenException::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            '\Exception',
            new ExpiredTokenException
        );
    }
}
