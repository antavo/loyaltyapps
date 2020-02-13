<?php
namespace Antavo\LoyaltyApps\Block\Frontend\IncentivizedPurchase;

use Antavo\LoyaltyApps\Helper\App\IncentivizedPurchase as IncentivizedPurchaseHelper;
use Antavo\LoyaltyApps\Helper\Checkout as CheckoutHelper;
use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 *
 */
class ProductPoints extends Template
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
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CheckoutHelper $checkoutHelper,
        ProductModel $productModel,
        IncentivizedPurchaseHelper $incentivizedPurchaseHelper,
        CurrencyModel $currencyModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutHelper = $checkoutHelper;
        $this->_incentivizedPurchaseHelper = $incentivizedPurchaseHelper;
        $this->_productModel = $productModel;
        $this->_currencyModel = $currencyModel;
    }

    /**
     * This method returns a product model from database by the given
     * product id; otherwise, returns a NULL.
     *
     * @param mixed $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function fetchProduct($productId)
    {
        $product = $this->_productModel->load($productId);

        if (!$product->getId()) {
            return NULL;
        }

        return $product;
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
     * It contains the custom attributes for Beerhawk, such as brewery, etc.
     *
     * @param ProductModel $product
     * @return array
     */
    public function exportProductProperties(ProductModel $product)
    {
        return array_merge(
            $this->_checkoutHelper->getCustomAttributes($product),
            [
                'product_id' => $product->getSku(),
                'product_name' => $product->getName(),
                'product_url' => $product->getUrlInStore(),
                'quantity' => 1,
                'subtotal' => $price = $this->calculateProductPrice($product),
                'sku' => $product->getSku(),
                'price' => $price,
                'discount' => 0,
                'product_category' => implode(
                    ', ',
                    $this->_checkoutHelper->getProductCategories($product)
                ),
            ]
        );
    }

    /**
     * This method created a mocked transaction object for calculating campaign bonuses.
     *
     * @param ProductModel $product
     * @return array
     */
    public function createMockedTransactionFor(ProductModel $product)
    {
        return [
            'total' => $this->calculateProductPrice($product),
            'transaction_id' => 'random_tx_' . time(),
            'items' => [$this->exportProductProperties($product)],
        ];
    }
}
