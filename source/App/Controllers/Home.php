<?php

namespace CD\App\Controllers;

use CD\Core\Controller;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\View;

class Home extends Controller
{
    static protected string $namespace = 'home';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . '/' . static::$namespace, static::$namespace);
    }

    public function indexAction()
    {
        Response::redirect('auth/login');
    }
}