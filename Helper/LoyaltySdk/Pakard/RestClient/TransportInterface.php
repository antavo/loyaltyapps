<?php
namespace Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient;

/**
 *
 */
interface TransportInterface {
    /**
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\RequestInterface $request
     * @param \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface $response
     * @return \Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient\ResponseInterface
     */
    public function send(RequestInterface $request, ResponseInterface $response);
}
