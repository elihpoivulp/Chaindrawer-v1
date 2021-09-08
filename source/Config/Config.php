<?php

namespace CD\Config;

class Config
{
    const WEBSITE_NAME = 'Chaindrawer';

    public static function DEBUG(): bool
    {
        // TODO Change value in production
        return true;
        // return $_ENV['DEBUG'];
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

    public static function EMAIL_NO_REPLY(): array
    {
        return [$_ENV['EMAIL_NOREPLY'], $_ENV['EMAIL_NOREPLY_PASS']];
    }

    public static function EMAIL_SMTP_HOST()
    {
        return $_ENV['EMAIL_SMTP_HOST'];
    }

    public const SLP_CONTRACT_ADDRESS = '0xCC8Fa225D80b9c7D42F96e9570156c65D6cAAa25';

    /**
     * Secret key for hashing
     * @var string
     */
    public const SECRET_KEY = 'Spv6TcDxCR15wRDTWKAc9Wg4TBsNfUuX';

    public const MAX_LOGIN_AGE = 60 * 60 * 24; // 1 day

    public const MANAGER_TERM = 'Manager';

    public const ADMIN_TERM = 'Admin';

    public const CHART_COLORS = [
        "#E3EBF6",
        "#2196F3",
        "#ed0b4b",
        "#00BCC2",
        "#E4A93C",
        "#66BB6A",
        "#824EE1",
        "#5567FF",
        "#19191A",
        "#95AAC9",
        "#B1BBC9",
        "#152E4D",
        "#e3f2fd",
        "#bbdefb",
        "#90c9f9",
        "#63b4f6",
        "#42a4f5",
        "#1f87e5",
        "#1a75d2",
        "#1764c0",
        "#fee3e9",
        "#fdb9c8",
        "#fa8ca3",
        "#f75c7f",
        "#f23764",
        "#dd024a",
        "#c80047",
        "#b40045",
        "#e8f5e9",
        "#c8e6c9",
        "#a5d6a7",
        "#81c784",
        "#4caf50",
        "#43a047",
        "#388e3c",
        "#2e7d32",
        "#383B3D",
        "#FFFFFF",
    ];

}