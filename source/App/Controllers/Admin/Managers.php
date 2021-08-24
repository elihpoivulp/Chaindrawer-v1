<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewManagerForm;
use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\Manager;

class Managers extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
        $this->loadModel('Managers');
    }

    public function indexAction()
    {
        $this->render('managers/all.html.twig', [
            'title' => 'Managers',
            'managers' => $this->model->getAllManagers()
        ]);
    }

    public function newAction()
    {
        $form = new NewManagerForm(new Manager());
        $this->render('managers/new.html.twig', [
            'title' => 'New Manager',
            'form' => $form
        ]);
    }
}