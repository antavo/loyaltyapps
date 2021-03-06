<?php
namespace Antavo\LoyaltyApps\Helper\LoyaltySdk\Pakard\RestClient;

/**
 * Exception thrown when response has a status code other than <tt>200 OK</tt>.
 *
 * Exception code holds the received HTTP status code, while message holds its
 * textual representation (e.g.: <tt>Bad Request</tt>).
 */
class StatusCodeException extends Exception {

}
