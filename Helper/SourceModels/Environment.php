<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\ObjectManager;

/**
 *
 */
class Environment implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $manager = ObjectManager::getInstance();
        /** @var \Magento\Framework\App\State $stateHelper */
        $stateHelper = $manager->get('Magento\Framework\App\State');

        if ($stateHelper::MODE_PRODUCTION == $stateHelper->getMode()) {
            return [
                ['value' => 'loyaltyStack1', 'label' => 'loyaltyStack1'],
                ['value' => 'loyaltyStack2', 'label' => 'loyaltyStack2'],
                ['value' => 'release', 'label' => 'releaseCandidate']
            ];
        } else {
            return [
                ['value' => 'development', 'label' => 'development'],
                ['value' => 'testing', 'label' => 'testing'],
                ['value' => 'release', 'label' => 'releaseCandidate'],
                ['value' => 'demo', 'label' => 'demo'],
                ['value' => 'salesDemo', 'label' => 'salesDemo'],
                ['value' => 'loyaltyStack1', 'label' => 'loyaltyStack1'],
                ['value' => 'loyaltyStack2', 'label' => 'loyaltyStack2']
            ];
        }
    }
}
