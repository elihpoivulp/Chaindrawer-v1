<?php

namespace CD\App\Controllers\Manager;

use CD\Config\Config;
use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use CD\Models\Teams as TeamsModel;

class Transactions extends ManagerViewOnly
{
    static protected string $template_namespace = 'manager';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$template_namespace, static::$template_namespace);
        $this->loadModel('AxiePayouts');
    }

    public function indexAction()
    {
        $this->render('transactions/index.html.twig', [
            'title' => 'Transactions',
            'account' => $this->account,
            'payouts' => $this->account->manager->getPayouts()
        ]);
    }
}