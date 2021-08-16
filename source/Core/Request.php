<?php

namespace CD\Core;

class Request
{
    protected string $current_path;

    public function __construct()
    {
        if(isset($_GET['_url'])){
            $this->current_path = $this->filter_input(INPUT_GET, '_url');
        } else {
            $this->current_path = $_SERVER['QUERY_STRING'] ?? $_SERVER['REQUEST_URI'];
        }
    }

    static public function getBaseURL(): string
    {
        return sprintf('%s%s', self::getProtocol(), $_SERVER['HTTP_HOST']);
    }

    static public function getProtocol(): string
    {
        return 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://';
    }

    static public function getSiteURL(): string
    {
        return URL_ROOT;
    }

    static public function getIPAddress(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    static public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function getPath(): string
    {
        return $this->current_path;
    }

    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet(): bool
    {
        return $this->method() === 'get';
    }

    public function isPost(): bool
    {
        return !$this->isGet();
    }

    public function getBody(): array
    {
        $body = [];
        $input_data = $this->isGet() ? [$_GET, INPUT_GET] : [$_POST, INPUT_POST];
        foreach ($input_data[0] as $key => $value) {
            $body[$key] = $this->filter_input($input_data[1], $key);
        }
        return $body;
    }

    private function filter_input(int $type, string $key): string
    {
        return filter_input($type, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}