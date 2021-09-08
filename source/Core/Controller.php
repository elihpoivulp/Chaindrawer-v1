<?php

namespace CD\Core;

use CD\Core\Forms\ModelForm;
use Exception;

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

    /**
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        $method = "{$name}Action";
        if (method_exists($this, $method)) {
            if ($this->before()) {
                $this->$method($arguments);
                $this->after();
            }
        } else {
            $controller_name = get_called_class();
            throw new Exception("Method '$method' in class '$controller_name' not found.", 404);
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
        $template_name = $this->getTemplate($template_name, $namespace);
        $this->view::renderTemplate($template_name, $context);
    }

    protected function getRender(string $template_name, array $context = [], ?string $namespace = null) {
        $template_name = $this->getTemplate($template_name, $namespace);
        return $this->view::getTemplate($template_name, $context);
    }

    private function getTemplate(string $template_name, ?string $namespace = null): string
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
        return $template_name;
    }

    protected function registerPath(string $directory = '', string $namespace = ''): void
    {
        $this->view::addPath($directory, $namespace);
    }
}