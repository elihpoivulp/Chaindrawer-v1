<?php

namespace CD\Core\Utils;

use CD\Config\Config;
use Exception;

class Token
{
    protected string $token;

    /**
     * @throws Exception
     */
    public function __construct(?string $token_value = null)
    {
        if (!is_null($token_value)) {
            $this->token = $token_value;
        } else {
            $this->token = bin2hex(random_bytes(16)); // 16 bytes = 128 bits = 32 hex characters
        }
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function getHash(): string
    {
        return hash_hmac('sha256', $this->getValue(), Config::SECRET_KEY);
    }
}