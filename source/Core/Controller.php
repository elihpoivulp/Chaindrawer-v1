<?php

namespace CD\Core;

use CD\Core\Forms\ModelForm;
use CD\Core\Models\Model;

class Controller
{
    static protected string $template_namespace = '';
    protected $model = null;
    protected string $modelNamespace = 'CD\\Models';
    protected array $params;
    protected View $view;
    protected Request $request;

    public function __construct(array $params, View $view, Request $request)
    {
        $this->params = $params;
        $this->view = $view;
        $this->request = $request;
    }


    public function loadModel(string $model_name)
    {
        $model =  $this->modelNamespace . '\\' . $model_name;
        $this->model = new $model();
    }

    public function getURIAsArray(): array
    {
        return explode('/', ltrim($this->request->getPath(), '/'));
    }

    public function getURIOnPos(int $index): ?string
    {
       return $this->getURIAsArray()[$index] ?? null;
    }

    public function __call(string $name, array $arguments)
    {
        $method = "{$name}Action";
        if (method_exists($this, $method)) {
            if ($this->before()) {
                $this->$method($arguments);
                $this->after();
            }
        } else {
            // TODO: Throw Exception
            // TODO: Replace with 404
            $controller_name = get_called_class();
            exit("Method '$method' in class '$controller_name' not found.");
        }
    }

    protected function before()
    {
        return true;
    }

    protected function after(): void
    {
    }

    protected function render(string $template_name, array $context = [], ?string $namespace = null): void
    {
        $namespace = $namespace ?? static::$template_namespace;
        if (!empty($namespace)) {
            if (str_starts_with($template_name, '@')) {
                // $template_name = preg_replace('/^@\w+\/([\w\d\/.]$)/', "@$namespace/$1", $template_name);
                $parts = explode('/', $template_name);
                $template_name = "@$namespace/{$parts[1]}";
            } else {
                $template_name = sprintf('@%s/%s', $namespace, $template_name);
            }
        }
        $this->view::renderTemplate($template_name, $context);
    }

    protected function registerPath(string $directory = '', string $namespace = ''): void
    {
        $this->view::addPath($directory, $namespace);
    }
}