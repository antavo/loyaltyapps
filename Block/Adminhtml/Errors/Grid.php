<?php
namespace Antavo\LoyaltyApps\Block\Adminhtml\Errors;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Magento\Backend\Helper\Data as DataHelper;
use Magento\Framework\Data\Form\FormKey as FormKeyHelper;
use Magento\Framework\View\Element\Template;

/**
 *
 */
class Grid extends Template
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
     * Returns the grid clear action URL for cleaning the API
     * error grid.
     *
     * @return string
     */
    public function getClearActionUrl()
    {
        return $this->_dataHelper->getUrl(
            'loyaltyapps/errors/clear',
            ['_secure' => TRUE]
        );
    }

    /**
     * Returns the Antavo log level by the extension configuration.
     * It is configurable on the admin side.
     *
     * @return string
     */
    public function getLogLevel()
    {
        return $this->_scopeConfig->getValue(ApiClient::XML_PATH_LOG_LEVEL);
    }

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
