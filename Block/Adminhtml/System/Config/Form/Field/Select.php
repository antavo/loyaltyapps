<?php
namespace Antavo\LoyaltyApps\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select as SelectParent;

/**
 * @deprecated
 */
class Select extends SelectParent
{
    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $this->{'setName'}($this->{'getInputName'}());
        $this->setClass('select');
        return parent::_toHtml();
    }
}
