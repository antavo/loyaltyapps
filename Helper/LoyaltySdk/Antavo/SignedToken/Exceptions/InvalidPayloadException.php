<?php
namespace Antavo\LoyaltyApps\Helper\LoyaltySdk\Antavo\SignedToken\Exceptions;

/**
 * Exception thrown when a token holds invalid payload data (it should be a PHP
 * array when unpacked). It is thrown from
 * {@see Antavo\SignedToken\SignedToken::setToken()}.
 */
class InvalidPayloadException extends Exception {}
