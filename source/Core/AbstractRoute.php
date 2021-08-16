<?php


namespace CD\Core;


abstract class AbstractRoute
{
    public ?string $controller = null;
    public ?array $routes = null;

    final public function __construct()
    {
        if (is_null($this->controller)) {
            // TODO: Throw Exception
            exit('Route controller must be defined.');
        }
        if (is_null($this->routes)) {
            // TODO: Throw Exception
            exit('Route paths must be defined.');
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