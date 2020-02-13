<?php
namespace Antavo\LoyaltyApps\Block\Adminhtml;

use Antavo\LoyaltyApps\Helper\ApiClient;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as DataHelper;
use Magento\Config\Block\System\Config\Form\Field as FieldParent;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\Storage;

/**
 *
 */
class ConnectButton extends FieldParent
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Antavo_LoyaltyApps::connect-button.phtml';

    /**
     * @var \Magento\MediaStorage\Model\File\Storage
     */
    protected $_fileStorage;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    protected $_apiClient;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Calculates the authorization URL through Antavo API Client.
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return $this->_apiClient->getOAuthAuthorizationUrl(
            $this->_dataHelper->getUrl(
                'loyaltyapps/config/connect',
                ['_secure' => TRUE]
            )
        );
    }

    /**
     * Transforms the Button widget to inline HTML.
     *
     * @return string
     */
    public function getButtonHtml()
    {
        try {
            return $this
                ->getButtonBlock()
                ->setData(
                    [
                        'id' => 'antavo_connect_button',
                        'label' => __('Sync settings'),
                    ]
                )
                ->toHtml();
        } catch (LocalizedException $e) {
            return NULL;
        }

    }

    /**
     * This method creates a new Button block for using its default markup.
     *
     * @return \Magento\Backend\Block\Widget\Button
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonBlock()
    {
        /** @var \Magento\Backend\Block\Widget\Button $block */
        $block = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        return $block;
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\MediaStorage\Model\File\Storage $fileStorage
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     * @param \Magento\Backend\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Storage $fileStorage,
        ApiClient $apiClient,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_fileStorage = $fileStorage;
        $this->_apiClient = $apiClient;
        $this->_dataHelper = $dataHelper;
    }
}
