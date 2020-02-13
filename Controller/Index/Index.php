<?php
namespace Antavo\LoyaltyApps\Controller\Index;

use Magento\Framework\App\Action\Action;

/**
 *
 */
class Index extends Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
