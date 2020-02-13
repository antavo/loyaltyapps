<?php
namespace Antavo\LoyaltyApps\Block\Adminhtml\Checkout;

use Magento\Backend\Helper\Data as DataHelper;
use Magento\Framework\Data\Form\FormKey as FormKeyHelper;
use Magento\Framework\View\Element\Template;

/**
 *
 */
class Form extends Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $_formKeyHelper;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $_dataHelper;

    /**
     * Returns a unique form key for validate form data
     * and avoid CSRF attacks.
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_formKeyHelper->getFormKey();
    }

    /**
     * Returns the order identifier from the pool
     * of the request parameters.
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * Returns the POST action URL for change the checkout
     * status in the Antavo database.
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_dataHelper->getUrl(
            'loyaltyapps/checkout/status',
            ['_secure' => TRUE]
        );
    }

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKeyHelper
     * @param \Magento\Backend\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FormKeyHelper $formKeyHelper,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_formKeyHelper = $formKeyHelper;
        $this->_dataHelper = $dataHelper;
    }
}
