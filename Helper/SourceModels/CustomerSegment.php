<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\ObjectManager;
use Magento\CustomerSegment\Model\ResourceModel\Segment\Collection;

/**
 *
 */
class CustomerSegment implements ArrayInterface
{
    /**
     * @return ObjectManager
     */
    private function getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if (!class_exists(Collection::class)) {
            return [];
        }

        /** @var Collection $collection */
        $collection = $this->getObjectManager()->get(Collection::class);
        return $collection->toOptionArray();
    }
}
