<?php
namespace Antavo\LoyaltyApps\Helper;

use Magento\Framework\App\Helper\AbstractHelper as MagentoAbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 *
 */
class Data extends MagentoAbstractHelper {
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer
    ) {
         parent::__construct($context);
         $this->storeManager = $storeManager;
         $this->registry = $registry;
         $this->serializer = $serializer;
    }

    /**
     * Returns all Antavo rates depending on "antavo_loyaltyapps/purchase/antavo_rates"
     * configuration.
     *
     * @return array
     */
    public function getAntavoRates()
    {
        return $this->serializer->unserialize($this->scopeConfig->getValue(
            'antavo_loyaltyapps/purchase/antavo_rates'
        ));
    }

    /**
     * @param int $store
     * @return int
     */
    public function getAntavoStoreRate($store)
    {
        $rates = $this->getAntavoRates();
        return isset($rates[$store]) ? (int) $rates[$store] : 1;
    }

    /**
     * Get product price according to antavo rate.
     *
     * @param int $price
     * @param int $store
     * @return int
     */
    public function getPriceAfterConversion($price, $store = NULL)
    {
        if (!isset($store)) {
            $store = $this->storeManager->getStore()->getId();
        }

        return $price * $this->getAntavoStoreRate($store);
    }
}
