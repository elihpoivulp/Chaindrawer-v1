<?php

namespace CD\Core;

use Exception;

class Router
{
    protected array $routes = [];
    protected array $params = [];
    protected string $current_path;
    protected string $default_controller = 'Home';
    protected string $default_action = 'index';
    protected string $controllers_namespace = 'CD\\App\\Controllers\\';

    /**
     * @throws Exception
     */
    public function dispatch(Request $request, View $view): void
    {
        $this->current_path = $this->removeSlashes($request->getPath());
        $result = $this->removeQueryString()->resolve();
        if ($result) {
            $controller_name = $this->params['controller'] ?? $this->default_controller;
            $controller_name = $this->toStudlyCaps($controller_name);
            $controller = $this->getNamespace() . $this->removeSlashes($controller_name);

            $action_name = $this->params['action'] ?? $this->default_action;
            $action = $this->toCamelCase($action_name);

            if (class_exists($controller)) {
                $controller_obj = new $controller($this->params, $view, $request);

                if (is_callable([$controller_obj, $action])) {
                    $action = empty($action) ? $this->default_action : $action;
                    $controller_obj->$action();
                } else {
                    throw new Exception("Method '$action' in class '$controller_name' not found.");
                }
            } else {
                throw new Exception("Controller class '$controller_name' not found.");
            }
        } else {
            throw new Exception("No routes matched.", 404);
        }
    }

    /**
     * @throws Exception
     */
    public function addRoute(string $route, array $params = [], ?string $routes_file = null): void
    {
        if (!is_null($routes_file)) {
            if (!str_starts_with($routes_file, '@')) {
                throw new Exception('$route_file namespace must be indicated with "@". Missing "@" character.');
            }
            $parts = explode('/', str_replace('@', '', $routes_file));
            $location = array_map(function (string $dirname) {
                return $this->toStudlyCaps($dirname);
            }, $parts);
            $namespace = $location[0];
            $routes = $this->getNamespace() . join('\\', $location);
            $route_obj = new $routes();
            foreach ($route_obj->getRoutes() as $path => $params) {
                $params['controller'] = $params['controller'] ?? $route_obj->getController();
                $params['namespace'] = $namespace;
                $this->routes[$this->cleanPath($route . '/' . $path)] = $params;
            }
        } else {
            $path = $this->cleanPath($route);
            $this->routes[$path] = $params;
        }
    }

    protected function cleanPath(string $path): string
    {
        if (empty($path)) {
            return '';
        }
        $path = $this->removeSlashes($path);
        return $this->compileURL($path);
    }

    protected function removeSlashes(string $url_path): string
    {
        return trim_slashes($url_path);
    }

    protected function resolve(): bool
    {
        if (empty($this->current_path) || preg_match('/^[?][^\w]+/', $this->current_path)) {
            return true;
        }
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $this->current_path, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $this->isPathConfigComplete($this->current_path, $params);
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    protected function removeQueryString(): self
    {
        if (!empty($this->current_path)) {
            $parts = explode('&', $this->current_path, 2);
            if (!str_contains($parts[0], '=')) {
                $this->current_path = $parts[0];
            } else {
                $this->current_path = '';
            }
        }
        return $this;
    }

    // TODO: Maybe convert this method to a helper function
    protected function toStudlyCaps(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }

    // TODO: Maybe convert this method to a helper function
    protected function toCamelCase(string $str): string
    {
        return lcfirst($this->toStudlyCaps($str));
    }

    private function getNamespace(): string
    {
        $namespace = $this->controllers_namespace;
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }

    private function compileURL(string $url_path): string
    {
        $url_path = str_replace('/', '\\/', $url_path);
        $url_path = preg_replace('/{([\w\-_]+)}/', '(?P<$1>[\w\-_]+)', $url_path);
        $url_path = preg_replace('/{([\w\-_]+):([^}]+)}/', '(?P<$1>$2)', $url_path);
        return '/^' . $url_path . '$/';
    }

    /**
     * @throws Exception
     */
    private function isPathConfigComplete(string $url_path, array $params): void
    {
        if (!str_contains($url_path, 'controller') && !array_key_exists('controller', $params)) {
            throw new Exception('No valid controller was configured for this route.');
        }
    }
    
}