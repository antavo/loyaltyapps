<?php
namespace Antavo\LoyaltyApps\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @abstract
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var mixed
     */
    protected $_class;

    /**
     * @return string
     */
    protected abstract function getClass();

    /**
     * @param array $arguments
     * @return mixed
     */
    protected function getClassMock(array $arguments = [])
    {
        $objectManager = new ObjectManager($this);
        return $objectManager->getObject($this->getClass(), $arguments);
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->_class = $this->getClassMock();
    }
}
