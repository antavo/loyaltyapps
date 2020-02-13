<?php
namespace Antavo\LoyaltyApps\Controller\Cart;

use Antavo\LoyaltyApps\Helper\Cart as CartHelper;
use Antavo\LoyaltyApps\Controller\ControllerTrait;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\Template as TemplateHelper;

/**
 *
 */
class Coupon extends Action
{
    use ControllerTrait;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Cart
     */
    private $_cartHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $_cartModel;

    /**
     * @var \Magento\Framework\View\Element\Template
     */
    private $_templateHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Antavo\LoyaltyApps\Helper\Cart $cartHelper
     * @param \Magento\Checkout\Model\Cart $cartModel
     * @param \Magento\Framework\View\Element\Template $templateHelper
     */
    public function __construct(
        Context $context,
        CartHelper $cartHelper,
        CartModel $cartModel,
        TemplateHelper $templateHelper
    ) {
        parent::__construct($context);
        $this->_cartHelper = $cartHelper;
        $this->_cartModel = $cartModel;
        $this->_templateHelper = $templateHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        // If the request is not a POST one, return
        if (!$request->isPost()) {
            $this->displayNotFound();
            return;
        }

        if ($this->_cartModel->getCustomerSession()->getId()) {
            // Getting points to burn from request.
            $pointsBurned = $request->getPost('points_burned');

            // Fallbacking empty input to NULL.
            if ("" == $pointsBurned || $pointsBurned < 0) {
                $pointsBurned = NULL;
            }

            // Creating, updating or deleting the current coupon; everything
            // happens inside the cart helper
            $this->_cartHelper->handleCartCoupon(
                $this->_cartModel,
                $pointsBurned
            );
        }

        // Redirecting to the cart page
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response
            ->setRedirect($this->_templateHelper->getUrl('checkout/cart'))
            ->sendHeaders()
            ->send();
        return;
    }
}
