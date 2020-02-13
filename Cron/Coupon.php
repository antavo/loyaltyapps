<?php
namespace Antavo\LoyaltyApps\Cron;

use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 *
 */
class Coupon {
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $_ruleCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $_iterator;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $iterator
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $ruleCollectionFactory,
        Iterator $iterator,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->_logger = $logger;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_iterator = $iterator;
        $this->_ruleRepository = $ruleRepository;
    }

    /**
     * This method deletes the outdated rule by the given id.
     *
     * @param array $args
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteCouponCallback(array $args)
    {
        $this->_ruleRepository->deleteById(
            $args['row']['rule_id']
        );
    }

    /**
     * Executes the daily cleanup on Antavo coupon set.
     *
     * @return $this
     */
    public function execute()
    {
        try {
            /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orders */
            // Getting all of expired rules from the database
            $rules = $this->_ruleCollectionFactory->create();
            $rules
                ->addFieldToFilter('name', 'Loyalty point burning coupon')
                ->addFieldToFilter('main_table.times_used', 0)
                ->addFieldToFilter(
                    'from_date',
                    [
                        'lteq' => date(
                            'Y-m-d',
                            strtotime('-2 days')
                        ),
                    ]
                );

            // Deleting the outdated rules
            $this->_iterator->walk(
                $rules->getSelect(),
                [[$this, 'deleteCouponCallback']]
            );
        } catch (\Exception $e) {
            $this->_logger->error(
                'Error occurred while deleting expired Antavo cart rule: ' . $e->getMessage()
            );
        }

        return $this;
    }
}
