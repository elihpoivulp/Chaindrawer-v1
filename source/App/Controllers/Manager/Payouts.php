<?php

namespace CD\App\Controllers\Manager;

use CD\Config\Config;
use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use CD\Models\Teams as TeamsModel;

class Payouts extends ManagerViewOnly
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
        $this->render('payouts/index.html.twig', [
            'title' => 'Payouts',
            'account' => $this->account,
            'payouts' => $this->account->manager->getPayouts()
        ]);
    }

    public function detailsAction()
    {
        // echo '<pre>';
        // print_r($this->account->manager->getPayout($this->params['id']));
        // echo '</pre>';
        // exit;
        $team_payout_details = $this->account->manager->getPayout($this->params['id']);
        $teams = new TeamsModel();
        $team = $teams->getTeamBySlug($team_payout_details['AssetTeamSlug']);
        // echo '<pre>';
        // print_r($team->getTeamManagers());
        // echo '</pre>';
        // exit;
        $this->render('payouts/details.html.twig', [
            'title' => 'Payout Details',
            'account' => $this->account,
            'p' => $team_payout_details,
            'm' => $team->getTeamManagers()
        ]);
    }
}