<?php
namespace Antavo\LoyaltyApps\Controller\Adminhtml\Errors;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 *
 */
class Show extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $_pageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Antavo API log');
        return $resultPage;
    }
}
