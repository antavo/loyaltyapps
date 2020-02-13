<?php
namespace Antavo\LoyaltyApps\Controller;

/**
 * @method \Magento\Framework\App\ResponseInterface getResponse()
 */
trait ServiceControllerTrait
{
    /**
     * Sends content in a JSON response with given HTTP status.
     *
     * @param mixed $content
     * @param int $statusCode
     */
    protected function sendJsonResponse($content, $statusCode = 200)
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response
            ->setHeader('Content-Type', 'application/json')
            ->setHttpResponseCode($statusCode)
            ->setBody(
                json_encode(
                    $content,
                    JSON_PRETTY_PRINT
                )
            );
    }

    /**
     * Creates error response body from exception.
     *
     * @param \Exception $exception
     * @return array
     */
    protected function getErrorResponse(\Exception $exception)
    {
        return [
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]
        ];
    }
}
