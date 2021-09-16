<?php

namespace CD\App\Controllers;

use CD\Core\Controller;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\View;

class Home extends Controller
{
    static protected string $template_namespace = 'home';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . '/' . static::$template_namespace, static::$template_namespace);
    }

    public function indexAction()
    {
        $this->render(
            'coming_soon.html.twig',
            [
                'title' => 'Coming Soon!'
            ]
        );
    }
}
