<?php
namespace Antavo\LoyaltyApps\Test\Unit\Controller\Index;

use Antavo\LoyaltyApps\Controller\Index\Index;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class IndexTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Index::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\App\Action\Action',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Controller\Index\Index::execute()
     */
    public function testExecute()
    {
        /** @var \Antavo\LoyaltyApps\Controller\Index\Index $class */
        $class = $this->getClassMock();
        $class->execute();
    }
}
