<?php
namespace Antavo\LoyaltyApps\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Store\Model\Store;

/**
 * @deprecated
 */
class PointCurrency extends AbstractFieldArray
{
    /**
     * @var \Antavo\LoyaltyApps\Block\Adminhtml\System\Config\Form\Field\Select
     */
    protected $_pageRenderer;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(Context $context, Factory $elementFactory, array $data = [])
    {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Antavo\LoyaltyApps\Block\Adminhtml\System\Config\Form\Field\Select
     */
    protected function createPageRenderer()
    {
        /** @var \Antavo\LoyaltyApps\Block\Adminhtml\System\Config\Form\Field\Select $renderer */
        $renderer = $this->getLayout()->createBlock(
            Select::class,
            '',
            ['data' => ['is_render_to_js_template' => TRUE]]
        );

        $renderer->setClass('store_selector');
        $renderer->setOptions(
            array_reduce(
                $this->_storeManager->getStores(),
                function (array $carry, Store $store) {
                    $carry[$store->getId()] = sprintf(
                        'WS-%s: %s - %s',
                        $store->getWebsiteId(),
                        $store->getName(),
                        $store->getCurrentCurrency()->getCode()
                    );
                    return $carry;
                },
                []
            )
        );
        return $renderer;
    }

    /**
     * Retrieve page column renderer
     *
     * @return \Antavo\LoyaltyApps\Block\Adminhtml\System\Config\Form\Field\Select
     */
    protected function getRenderer()
    {
        if (!isset($this->_pageRenderer)) {
            $this->_pageRenderer = $this->createPageRenderer();
        }

        return $this->_pageRenderer;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('store', ['label' => __('Store'), 'renderer'=> $this->getRenderer()]);
        $this->addColumn('rate', ['label' => __('Points rate')]);
        $this->_addAfter = FALSE;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->getRenderer()->calcOptionHash($row->getStore())] = 'selected="selected"';

        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}
