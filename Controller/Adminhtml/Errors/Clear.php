<?php
namespace Antavo\LoyaltyApps\Controller\Adminhtml\Errors;

use Antavo\LoyaltyApps\Helper\Logger;
use Magento\Backend\App\Action;
use Magento\Framework\HTTP\Header as HttpHeaderHelper;

/**
 *
 */
class Clear extends Action
{
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $_httpHeaderHelper;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\HTTP\Header $httpHeaderHelper
     */
    public function __construct(
        Action\Context $context,
        HttpHeaderHelper $httpHeaderHelper
    ) {
        parent::__construct($context);
        $this->_httpHeaderHelper = $httpHeaderHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        if ($request->isPost() && file_put_contents(Logger::getFilePath(), '') === FALSE) {
            $this->messageManager->addError('Could not empty logfile');
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response
            ->setRedirect($this->_httpHeaderHelper->getHttpReferer())
            ->sendHeaders()
            ->send();
        return;
    }
}
