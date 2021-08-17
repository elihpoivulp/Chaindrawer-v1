<?php

namespace CD\App\Controllers\Manager;

use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;

class Teams extends ManagerViewOnly
{
    static protected string $namespace = 'manager';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$namespace, static::$namespace);
    }

    public function indexAction()
    {
        if ($this->user->manager) {
            $account = $this->user;
        } else {
            // TODO: Show error instead of exit
            exit('This user has a Manager role but has no Manager Account');
        }
        $this->render('teams/my_teams.html.twig', [
            'title' => 'My Teams',
            'account' => $account
        ]);
    }

}