<?php


namespace CD\App\Controllers\Admin;

use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;

class Admin extends AdminViewOnly
{
    static protected string $namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$namespace, static::$namespace);
        $this->loadModel('AuthenticationModel');
    }

    public function indexAction()
    {
        $this->render('index.html.twig', context: [
            'title' => 'Admin'
        ]);
    }
}