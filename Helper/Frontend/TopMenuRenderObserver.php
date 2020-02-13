<?php
namespace Antavo\LoyaltyApps\Helper\Frontend;

use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Url as UrlHelper;

/**
 * This class handles one main Magento event:
 *  - page_block_html_topmenu_gethtml_before
 */
class TopMenuRenderObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Magento\Framework\Url
     */
    private $_urlHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Url $urlHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        UrlHelper $urlHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_urlHelper = $urlHelper;
    }

    /**
     * @param \Magento\Framework\Data\Tree\Node $parent
     * @return \Magento\Framework\Data\Tree\Node
     */
    private function createMenuEntry(Node $parent)
    {
        return new Node(
            [
                'name' => 'Loyalty Central',
                'id' => 'loyalty_central_link',
                'url' => $this->_customerSession->isLoggedIn()
                    ? $this->_urlHelper->getUrl('loyaltycentral/index/index')
                    : sprintf(
                        "javascript: Antavo.Popup.display('%s');",
                        $this->_scopeConfig->getValue(
                            AntavoConfigInterface::XML_PATH_LOYALTY_CENTRAL_URL
                        )
                    )
            ],
            'id',
            $parent->getTree(),
            $parent
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->_scopeConfig->getValue(AntavoConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
            return TRUE;
        }

        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getData('menu');
        $menu->addChild($this->createMenuEntry($menu));
        return TRUE;
    }
}
