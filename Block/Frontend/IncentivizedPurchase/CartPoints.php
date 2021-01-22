<?php
namespace Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase;

use Antavo\LoyaltyApps\Helper\App\IncentivizedPurchase as IncentivizedPurchaseHelper;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Item;

/**
 *
 */
class CartPoints extends Template
{
    /**
     * @var \Antavo\LoyaltyApps\Helper\Checkout
     */
    private $_checkoutHelper;

    /**
     * @var \Antavo\LoyaltyApps\Helper\App\IncentivizedPurchase
     */
    private $_incentivizedPurchaseHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_productModel;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $_currencyModel;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $_cartModel;

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    public function getCartModel() {
        return $this->_cartModel;
    }

    /**
     * Returns the current store's currency code.
     *
     * @return string
     */
    private function getStoreCurrencyCode()
    {
        return $this->_storeManager
            ->getStore()
            ->{'getCurrentCurrency'}()
            ->getCode();
    }

    /**
     * Returns the current store's base currency code.
     *
     * @return string
     */
    private function getStoreBaseCurrencyCode()
    {
        return $this->_storeManager
            ->getStore()
            ->{'getBaseCurrency'}()
            ->getCode();
    }

    /**
     * @return bool
     */
    private function isCurrencyConversionEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(
            AntavoConfigInterface::XML_PATH_CONVERT_CURRENCY,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_incentivizedPurchaseHelper->isEnabled();
    }

    /**
     * @param Template\Context $context
     * @param \Antavo\LoyaltyApps\Helper\Checkout $checkoutHelper
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Antavo\LoyaltyApps\Helper\App\IncentivizedPurchase $incentivizedPurchaseHelper
     * @param \Magento\Directory\Model\Currency $currencyModel
     * @param \Magento\Checkout\Model\Cart $cartModel
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CheckoutHelper $checkoutHelper,
        ProductModel $productModel,
        IncentivizedPurchaseHelper $incentivizedPurchaseHelper,
        CurrencyModel $currencyModel,
        CartModel $cartModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutHelper = $checkoutHelper;
        $this->_incentivizedPurchaseHelper = $incentivizedPurchaseHelper;
        $this->_productModel = $productModel;
        $this->_currencyModel = $currencyModel;
        $this->_cartModel = $cartModel;
    }

    /**
     * Calculates the price of the given product. Also it converts the price
     * to the base currency, if necessary -- it is configurable on the admin side.
     *
     * @param ProductModel $product
     * @return float
     */
    public function calculateProductPrice(ProductModel $product)
    {
        $price = $product->getFinalPrice();
        $storeCurrency = $this->getStoreCurrencyCode();
        $baseCurrency = $this->getStoreBaseCurrencyCode();

        if ($storeCurrency != $baseCurrency && $this->isCurrencyConversionEnabled()) {
            $currency_instance = $this->_currencyModel->load($baseCurrency);
            $price *= $currency_instance->getRate($storeCurrency);
        }

        return $price;
    }

    /**
     * Exports the product properties for campaign bonus calculation.
     *
     * @param Item $item
     * @return array
     */
    public function exportCartItemProperties(Item $item)
    {
        return [
            'product_id' => $item->getProduct()->getSku(),
            'product_name' => $item->getName(),
            'product_url' => $item->getProduct()->getUrlInStore(),
            'discount' => $discount = $item->getDiscountAmount() ?: 0,
            'price' => $unitPrice = $this->calculateProductPrice($item->getProduct()),
            'subtotal' => $unitPrice * $item->getQty() - $discount,
            'sku' => $item->getSku(),
            'quantity' => $item->getQty(),
            'product_category' => implode(
                ', ',
                $this->_checkoutHelper->getProductCategories($item->getProduct())
            ),
        ];
    }

    /**
     * This method created a mocked transaction object for calculating campaign bonuses.
     *
     * @param Quote $quote
     * @return array
     */
    public function createMockedTransactionFor(Quote $quote)
    {
        return array_reduce(
            $quote->getAllVisibleItems(),
            function (array $carry, Item $item) use ($quote) {
                $carry['total'] = $quote->getSubtotal();
                $carry['transaction_id'] = 'random_tx_' . time();
                $carry['items'][] = $this->exportCartItemProperties($item);
                return $carry;
            },
            []
        );
    }
}
