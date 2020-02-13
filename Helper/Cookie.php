<?php
namespace Antavo\LoyaltyApps\Helper;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 *
 */
class Cookie
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @param int|null $duration
     * @return \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata
     */
    public function createPublicCookieMetadata($duration = NULL) {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->getMainDomain($this->_sessionManager->getCookieDomain()));

        if (isset($duration)) {
            $metadata->setDuration($duration);
        }

        return $metadata;
    }

    /**
     * @param string $domain
     * @return string
     * @static
     */
    public static function getMainDomain($domain) {
        return implode('.', array_slice(
            explode('.', $domain),
            preg_match('/co\.[a-z]{2}|com.au$/', $domain) ? -3 : -2
        ));
    }

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
    }

    /**
     * @param string $name
     * @return string
     */
    public function get($name)
    {
        return $this->_cookieManager->getCookie($name);
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $duration
     * @return $this
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException If cookie couldn't be sent to the browser.
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws \Magento\Framework\Exception\InputException If the cookie name is empty or contains invalid characters.
     */
    public function set($name, $value, $duration = 86400)
    {
        $this->_cookieManager->setPublicCookie(
            $name,
            $value,
            $this->createPublicCookieMetadata($duration)
        );
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException If cookie
     * couldn't be sent to the browser. If this exception isn't thrown, there is
     * still no guarantee that the browser received and accepted the request to
     * delete this cookie.
     * @throws \Magento\Framework\Exception\InputException If the cookie name is
     * empty or contains invalid characters.
     */
    public function delete($name)
    {
        $this->_cookieManager->deleteCookie($name, $this->createPublicCookieMetadata());
        return $this;
    }
}
