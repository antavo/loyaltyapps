<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\ObjectManager;

/**
 *
 */
class Region implements ArrayInterface
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
                ['value' => 'st1', 'label' => 'st1'],
                ['value' => 'st2', 'label' => 'st2'],
                ['value' => 'rc', 'label' => 'rc']
            ];
        } else {
            return [
                ['value' => 'dev', 'label' => 'dev'],
                ['value' => 'test', 'label' => 'test'],
                ['value' => 'rc', 'label' => 'rc'],
                ['value' => 'demo', 'label' => 'demo'],
                ['value' => 'salesdemo', 'label' => 'salesdemo'],
                ['value' => 'st1', 'label' => 'st1'],
                ['value' => 'st2', 'label' => 'st2']
            ];
        }
    }
}
