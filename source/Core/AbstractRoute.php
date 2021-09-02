<?php


namespace CD\Core;


use Exception;

abstract class AbstractRoute
{
    public ?string $controller = null;
    public ?array $routes = null;

    /**
     * @throws Exception
     */
    final public function __construct()
    {
        if (is_null($this->controller)) {
            throw new Exception('Route controller must be defined.');
        }
        if (is_null($this->routes)) {
            throw new Exception('Route paths must be defined.');
        }
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}