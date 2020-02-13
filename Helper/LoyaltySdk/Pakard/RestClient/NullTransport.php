<?php
namespace Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient;

/**
 * Transport that does nothing, really. There may be edge cases when it comes
 * handy.
 */
class NullTransport implements TransportInterface {
    /**
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\RequestInterface $request
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface $response
     * @return \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface
     */
    public function send(RequestInterface $request, ResponseInterface $response) {
        return $response->setStatusCode(ResponseInterface::STATUS_CODE_OK);
    }
}
