<?php
namespace Antavo\LoyaltyApps\Helper\App;

use Antavo\LoyaltyApps\Helper\ApiClient as AntavoApiClient;
use Antavo\LoyaltyApps\Helper\ConfigInterface as AntavoConfigInterface;
use Magento\Backend\Model\View\Result\Redirect as RedirectModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\Review as ReviewModel;

/**
 *
 */
class Reviews implements AppInterface
{
    /**
     * @var string
     */
    const XML_PATH_ENABLED = 'antavo_loyaltyapps/reviews/enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $_httpRequest;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    private $_redirectModel;

    /**
     * @var \Magento\Review\Model\Review
     */
    private $_reviewModel;

    /**
     * @var \Antavo\LoyaltyApps\Helper\ApiClient
     */
    private $_apiClient;

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED
        );
    }

    /**
     * This methods returns the review module's status. It is configurable
     * on the admin side.
     *
     * @return bool
     */
    private function isReviewSendingEnabled()
    {
        return $this->_scopeConfig->getValue(
            AntavoConfigInterface::XML_PATH_REVIEW_EVENT_SENDING,
            ScopeInterface::SCOPE_STORES,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * Loads review object from database. Returns a NULL, if requested
     * review does not exists.
     *
     * @param string $reviewId
     * @return \Magento\Review\Model\Review
     */
    private function loadReview($reviewId)
    {
        $review = $this->_reviewModel->load($reviewId);

        if (!$review->getId()) {
            return NULL;
        }

        return $review;
    }

    /**
     * Sends review_submit event with prepared details.
     *
     * @param \Magento\Review\Model\Review $review
     * @throws \Exception
     */
    private function sendReviewEvent(ReviewModel $review)
    {
        try {
            $this->_apiClient->sendEvent(
                $review->getData('customer_id'),
                'review_submit',
                [
                    'item' => $review->getData('entity_pk_value'),
                    'review' => $review->getData('detail'),
                    'review_id' => $review->getData('review_id'),
                    'provider' => 'Magento2'
                ]
            );
        } catch (\Exception $e) {
            // Failing silently...
        }
    }

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Backend\Model\View\Result\Redirect $redirectModel
     * @param \Magento\Review\Model\Review $reviewModel
     * @param \Antavo\LoyaltyApps\Helper\ApiClient $apiClient
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        HttpRequest $httpRequest,
        RedirectModel $redirectModel,
        ReviewModel $reviewModel,
        AntavoApiClient $apiClient
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_httpRequest = $httpRequest;
        $this->_redirectModel = $redirectModel;
        $this->_reviewModel = $reviewModel;
        $this->_apiClient = $apiClient;
    }

    /**
     * Invoking after executing a review save event. By design, we actually
     * can invoke custom functions before/after internal methods. These methods
     * must be defined in di.xml file.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function afterExecute()
    {
        $review = NULL;

        try {
            // If the extension is not enabled yet, return
            if (!$this->_scopeConfig->getValue(AntavoConfigInterface::XML_PATH_PLUGIN_ENABLED)) {
                return NULL;
            }

            // If review event sending is turned off, return
            if (!$this->isReviewSendingEnabled()) {
                return NULL;
            }

            $reviewStatusId = $this->_httpRequest->getParam('status_id');

            // If status is not approved yet, return
            if (ReviewModel::STATUS_APPROVED != $reviewStatusId) {
                return NULL;
            }

            $reviewId = $this->_httpRequest->getParam('id');

            // If the status is not fetchable from database, return
            if (!($review = $this->loadReview($reviewId))) {
                return NULL;
            }

            // If the review is not associated to a customer, return
            if (!$review->getData('customer_id')) {
                return NULL;
            }

            // Sending in the review event
            $this->sendReviewEvent($review);
        } catch (\Exception $e) {
            // Failing silently...
        } finally {
            // Creating a redirect model for using that as a response
            $redirect = $this->_redirectModel->setPath('review/*');

            if (isset($review)) {
                $redirect->setPath('review/*/edit', ['id' => $review->getId()]);
            }

            return $redirect;
        }
    }
}
