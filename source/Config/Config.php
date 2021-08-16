<?php

namespace CD\Config;

class Config
{
    const WEBSITE_NAME = 'Chaindrawer';

    public static function DEBUG(): bool
    {
        return $_ENV['DEBUG'];
    }

    public static function LOGS_DIR(): string
    {
        return BASE_PATH . '/logs';
    }

    public static function DB_DSN()
    {
        return $_ENV['DB_DSN'];
    }

    public static function DB_USER()
    {
        return $_ENV['DB_USER'];
    }

    public static function DB_PASS()
    {
        return $_ENV['DB_PASS'];
    }

    public const SLP_CONTRACT_ADDRESS = '0xCC8Fa225D80b9c7D42F96e9570156c65D6cAAa25';

    /**
     * Secret key for hashing
     * @var string
     */
    public const SECRET_KEY = 'true4espMnrT2QfZqbotQKHE97RZDgpCmBWR';

    public const MAX_LOGIN_AGE = 60 * 60 * 24; // 1 day

    public const MANAGER_TERM = 'Manager';
    public const ADMIN_TERM = 'Admin';
}