<?php


namespace CD\App\Controllers\Admin;

use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\Manager;
use CD\Models\Teams as TeamsModel;

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
        $manager = new Manager();
        $teams = new TeamsModel();
        $this->render('index.html.twig', [
            'title' => 'Admin',
            'withdrawals' => $manager->getAllWithdrawals(5),
            'incoming_claims' => $teams->getIncomingClaims()
        ]);
    }
}