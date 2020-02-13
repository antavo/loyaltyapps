<?php
namespace Antavo\LoyaltyApps\Helper;

use Antavo\LoyaltyApps\Helper\Token\ExpiredTokenException;
use Antavo\LoyaltyApps\Helper\Token\InvalidTokenException;

/**
 * Packs/unpacks a signed token with custom data.
 */
class Token {
    /**
     * @var callable
     * @link http://php.net/function.hash_algos
     */
    public $algorithm = 'sha1';

    /**
     * @var string
     * @link https://xkcd.com/936/
     */
    public $secret = 'correct horse battery staple';

    /**
     * @var int  Expiraton time as Unix timestamp. If its value is smaller than
     *   30 days (in seconds) then it is handled as a relative, time-to-live
     *   value. 0 = never expires.
     */
    public $expires_at = 0;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param string
     * @param int
     */
    public function __construct($secret = NULL, $expires_at = NULL)
    {
        if (isset($secret)) {
            $this->secret = $secret;
        }

        if (isset($expires_at)) {
            $this->expires_at = $expires_at;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * @return string
     */
    public function get()
    {
        if (!isset($this->token)) {
            // Packing data, calculating expiration time.
            $data = $this->data;
            $expires_at = $this->expires_at && ($this->expires_at <= 2592000)
                ? time() + $this->expires_at
                : $this->expires_at
            ;

            if (!is_scalar($data)) {
                settype($data, 'array');
                if ($expires_at > 0) {
                    $data['expires_at'] = $expires_at;
                }
                $data = json_encode($data);
            }

            // Calculating & packing digest (hashing, base64 encoding, trimming
            // padding characters, converting to URI-safe).
            $digest = strtr(trim(base64_encode(hash_hmac(
                $this->algorithm,
                $data,
                $this->secret
            )), '='), '+/', '-_');

            // Converting data to Base64, trimming padding characters,
            // converting to URI-safe.
            $data = strtr(trim(base64_encode($data), '='), '+/', '-_');

            $this->token = $digest . '.' . $data;
        }

        return $this->token;
    }

    /**
     * @param string
     * @return self
     * @throws \Exception
     */
    public function set($token)
    {
        $this->token = $token;

        // Replacing URI-safe symbols with their originals.
        $data = strtr($token, '-_', '+/');

        $digest = base64_decode(strtok($data, '.'));
        $payload = base64_decode(strtok('.'));

        if (hash_hmac($this->algorithm, $payload, $this->secret) != $digest) {
            throw new InvalidTokenException;
        }

        $this->data = json_decode($payload, true);

        if (json_last_error()) {
            $this->data = $payload;
        }

        // Checking token expiration.
        if (
            isset($this->data['expires_at'])
            && $this->data['expires_at'] > 0
            && $this->data['expires_at'] < time()
        ) {
            throw new ExpiredTokenException;
        }

        return $this;
    }

    /**
     * @param array
     * @return static
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
