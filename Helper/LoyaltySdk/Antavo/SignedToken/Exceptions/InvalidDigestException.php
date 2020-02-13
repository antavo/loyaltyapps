<?php
namespace Antavo\LoyaltyApps\Helper\LoyaltySdk\Antavo\SignedToken\Exceptions;

/**
 * Exception thrown when token integrity check fails in
 * {@see Antavo\SignedToken\SignedToken::setToken()}.
 */
class InvalidDigestException extends Exception {}
