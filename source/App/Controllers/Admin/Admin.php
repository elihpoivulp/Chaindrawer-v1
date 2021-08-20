<?php


namespace CD\App\Controllers\Admin;

use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;

class Admin extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
        $this->loadModel('AuthenticationModel');
    }

    public function indexAction()
    {
        $this->render('index.html.twig', [
            'title' => 'Admin'
        ]);
    }
}