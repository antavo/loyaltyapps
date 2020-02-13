<?php
namespace Antavo\LoyaltyApps\Helper;

use Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\CurlTransport;
use Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface;
use Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient;
use Antavo\LoyaltyApps\Helper\Config as ConfigHelper;
use Antavo\LoyaltyApps\Helper\Logger as LoggerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl as Client;

/**
 *
 */
class ApiClient extends Client
{
    /**
     * @var string
     */
    const XML_PATH_API_KEY = 'antavo_loyaltyapps/api/key';

    /**
     * @var string
     */
    const XML_PATH_API_SECRET = 'antavo_loyaltyapps/api/secret';

    /**
     * @var string
     */
    const XML_PATH_API_URL = 'antavo_loyaltyapps/api/base_url';

    /**
     * @var string
     */
    const XML_PATH_OAUTH_CLIENT_ID = 'antavo_loyaltyapps/api/oauth_client_id';

    /**
     * @var string
     */
    const XML_PATH_OAUTH_CLIENT_SECRET = 'antavo_loyaltyapps/api/oauth_client_secret';

    /**
     * @var string
     */
    const XML_PATH_LOG_LEVEL = 'antavo_loyaltyapps/api/log_level';

    /**
     * @var string
     */
    const LOG_LEVEL_ALL = 'all';

    /**
     * Note: this is from Loyalty
     *
     * @var int
     */
    const INTERRUPT_EXCEPTION = 127201;

    /**
     * @var string
     */
    const LOG_LEVEL_ERRORS = 'errors';

    /**
     * @var string
     */
    const LOG_LEVEL_NONE = '';

    /**
     * @var \Antavo\LoyaltyApps\Helper\Config
     */
    private $_configHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Antavo\LoyaltyApps\Helper\Logger
     */
    private $_loggerHelper;

    /**
     * @var string
     */
    protected $_baseUri;

    /**
     * @var string
     */
    protected $_clientId;

    /**
     * @var string
     */
    protected $_clientSecret;

    /**
     * @var string
     */
    protected $_key;

    /**
     * @var string
     */
    protected $_secret;

    /**
     * @var string
     */
    protected $_region;

    /**
     * @var \Exception
     */
    protected $_error;

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->_baseUri;
    }

    /**
     * @param string $baseUri
     * @return $this
     */
    public function setBaseUri($baseUri)
    {
        $this->_baseUri = $baseUri;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @param string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->_clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    /**
     * @param string $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->_clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->_region;
    }

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->_region = $region;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * @param string $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
        return $this;
    }

    /**
     * @return \Exception
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param \Exception $error
     * @return $this
     */
    public function setError(\Exception $error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * @return array
     * @static
     */
    public static function getLogLevelOptions()
    {
        return [
            self::LOG_LEVEL_ALL => 'All requests',
            self::LOG_LEVEL_ERRORS => 'Errors only',
            self::LOG_LEVEL_NONE => 'None',
        ];
    }

    /**
     * @param \Antavo\LoyaltyApps\Helper\Config $configHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Antavo\LoyaltyApps\Helper\Logger $loggerHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        ScopeConfigInterface $scopeConfig,
        LoggerHelper $loggerHelper
    ) {
        $this->_configHelper = $configHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_loggerHelper = $loggerHelper;

        // Initializing ApiClient with credentials & other API stuffs
        $this
            ->setBaseUri($configHelper->getEnvironment()->getApiUrl())
            ->setKey($scopeConfig->getValue(self::XML_PATH_API_KEY))
            ->setSecret($scopeConfig->getValue(self::XML_PATH_API_SECRET))
            ->setRegion($scopeConfig->getValue(ConfigInterface::XML_PATH_REGION))
            ->setClientId($configHelper->getEnvironment()->getClientId())
            ->setClientSecret($configHelper->getEnvironment()->getClientSecret());
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeader($name)
    {
        if (isset($this->_responseHeaders[$name])) {
            return $this->_responseHeaders[$name];
        }

        return NULL;
    }

    /**
     * @param string $body
     * @return mixed
     * @throws \Exception
     */
    protected function prepareBody($body)
    {
        switch (strtok($this->getHeader('Content-Type'), ';')) {
            case 'application/json':
                $parsedBody = json_decode($body);

                if (($error = json_last_error()) !== JSON_ERROR_NONE) {
                    throw $this->setError(
                        new \Exception(
                            json_last_error_msg(), $error, $this->getError()
                        )
                    )->getError();
                }

                return $parsedBody;
            default:
                return $body;
        }
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function prepareUri($uri)
    {
        if (!preg_match('#https{0,1}://#', $uri)) {
            return rtrim($this->_baseUri, '/') . '/' . ltrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Make request
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    protected function makeRequest($method, $uri, $params = [])
    {
        $uri = $this->prepareUri($uri);

        if (($key = $this->getKey()) !== NULL) {
            if ('POST' == strtoupper($method)) {
                $params['api_key'] = $key;
            } else {
                $uri .= (strpos($uri, '?') === FALSE ? '?' : '&') . 'api_key=' . $key;
            }
        }

        $body = NULL;

        $this->before($method, $uri, $params);

        $client = NULL;

        try {
            // Antavo PHP SDK handles the request
            $client = (new RestClient(
                $this->getRegion(),
                $this->getKey(),
                $this->getSecret()
            ));
            $client->setTransport(new CurlTransport);
            $body = $this->prepareBody($client->send($method, $uri, $params));

            if (isset($body->error)) {
                // interrupt exceptions are skipped
                if ($body->error->code != self::INTERRUPT_EXCEPTION) {
                    throw $this->setError(
                        new \Exception(
                            isset($body->error->message)
                                ? $body->error->message
                                : 'Unknown error',
                            isset($body->error->code)
                                ? $body->error->code
                                : 0
                        )
                    )->getError();
                }
            }
        } catch (\Exception $e) {
            $this->setError($e);
        }

        // pulling off api key from the request, since making it with Escher token
        if (isset($params['api_key'])) {
            unset($params['api_key']);
        }

        // Logging response
        $this->after(
            $method,
            $uri,
            $params,
            $this->getClientData($client)
        );

        if ($error = $this->getError()) {
            throw $error;
        }

        return $body;
    }

    /**
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\RestClient|null $client
     * @return array
     */
    protected function getClientData($client) {
        $data = [
            'request_headers' => NULL,
            'response_headers' => NULL,
            'body' => NULL,
            'status_code' => ResponseInterface::STATUS_CODE_INTERNAL_SERVER_ERROR
        ];

        if (isset($client)) {
            if ($request = $client->getRequest()) {
                $data['request_headers'] = $request->getHeadersSent();
            }

            if ($response = $client->getResponse()) {
                $data['body'] = $response->getRawBody();
                $data['response_headers'] = $response->getRawHeaders();
                $data['status_code'] = $response->getStatusCode();
            }
        }

        return $data;
    }

    /**
     * Make GET request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     * @return mixed
     * @throws \Exception
     */
    public function get($uri)
    {
        return $this->makeRequest("GET", $uri);
    }

    /**
     * Make POST request
     *
     * @param string $uri
     * @param array $params
     * @return mixed
     * @throws \Exception
     * @see lib/Mage/HTTP/Mage_HTTP_Client#post($uri, $params)
     */
    public function post($uri, $params)
    {
        return $this->makeRequest("POST", $uri, $params);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     */
    protected function before($method, $uri, array $params)
    {

    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param array $data
     * @return mixed
     */
    protected function after($method, $uri, $params, $data = [])
    {
        switch ($this->_scopeConfig->getValue(self::XML_PATH_LOG_LEVEL)) {
            case self::LOG_LEVEL_NONE:
                return NULL;
            case self::LOG_LEVEL_ERRORS:
                if ($this->getError()) {
                    return $this->_loggerHelper->err(
                        $this->createLog($method, $uri, $params, $data)
                    );
                }

                break;
            case self::LOG_LEVEL_ALL:
                if ($this->getError()) {
                    return $this->_loggerHelper->err(
                        $this->createLog($method, $uri, $params, $data)
                    );
                }

                return $this->_loggerHelper->info(
                    $this->createLog($method, $uri, $params, $data)
                );
            default:
                // It's an invalid value, but nevermind.
                return NULL;
        }
    }

    /**
     * Helper method to separate type info and error easily.
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param array $data
     * @return string
     */
    protected function createLog($method, $uri, $params, $data)
    {
        $response = [
            'request' => [
                'method' => $method,
                'url' => $uri,
                'header' => NULL,
                'body' => http_build_query($params) ?: NULL
            ],
            'response' => [
                'status' => ResponseInterface::STATUS_CODE_INTERNAL_SERVER_ERROR,
                'header' => NULL,
                'body' => NULL
            ],
        ];

        array_walk(
            $data,
            function ($value, $key) use (&$response) {
                switch (TRUE) {
                    case 'request_headers' == $key && is_array($value):
                        array_walk(
                            $value,
                            function ($value, $key) use (&$response) {
                                $response['request']['header'] .= $key . ': ' . $value . "\r\n";
                            }
                        );

                        break;
                    case 'response_headers' == $key && is_string($value):
                        $response['response']['header'] = $value;
                        break;
                    case 'body' == $key && is_string($value):
                        $response['response']['body'] = $value;
                        break;
                    case 'status_code' == $key && is_int($value):
                        $response['response']['status'] = $value;
                        break;
                    default:
                        break; // invalid value, do nothing
                }
            }
        );

        return json_encode($response);
    }

    /**
     * @param int $customerId
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws \Exception
     * @todo [ljozs3f][2018-10-01] check if guest customer id hashing logic is fine
     */
    public function sendEvent($customerId, $action, array $data = [])
    {
        return $this->post(
            '/events',
            [
                'action' => $action,
                'customer' => (string) $customerId ?: 'opted_out_' . time() . ':' . sha1('guest'),
                'data' => $data
            ]
        );
    }

    /**
     * Returns Antavo OAuth authorization URL.
     *
     * @param string $redirectUri
     * @return string
     */
    public function getOAuthAuthorizationUrl($redirectUri)
    {
        return sprintf(
            '%s/oauth/authorize?%s',
            rtrim($this->_baseUri, '/'),
            http_build_query(
                [
                    'client_id' => $this->getClientId(),
                    'redirect_uri' => $redirectUri,
                ]
            )
        );
    }

    /**
     * @param string $code
     * @param string $redirectUri
     * @return mixed
     * @throws \Exception
     */
    public function getOAuthAccessToken($code, $redirectUri)
    {
        return $this->get(
            '/oauth/access_token?' . http_build_query(
                [
                    'client_id' => $this->getClientId(),
                    'client_secret' => $this->getClientSecret(),
                    'code' => $code,
                    'redirect_uri' => $redirectUri
                ]
            )
        );
    }
}
