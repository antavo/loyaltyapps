<?php
namespace Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient;

/**
 *
 */
class MockTransport implements TransportInterface {
    /**
     * @var callable
     */
    protected $_callback;

    /**
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\RequestInterface $request
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface $response
     * @return \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface
     */
    public function send(RequestInterface $request, ResponseInterface $response) {
        if (!is_null($callback = $this->_callback)) {
            $callback($request, $response);
        }

        return $response;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function setCallback(callable $callback) {
        $this->_callback = $callback;
        return $this;
    }
}
