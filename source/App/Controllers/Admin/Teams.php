<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewTeamForm;
use CD\Core\Request;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\AssetPlatformModel;
use CD\Models\PlayerModel;

class Teams extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
        $this->loadModel('TeamModel');
    }

    public function indexAction()
    {
        $teams = $this->model->getTeams();
        $this->render('teams/all.html.twig', [
            'title' => 'Teams',
            'teams' => $teams
        ]);
    }

    public function newTeamAction()
    {
        $form = new NewTeamForm($this->model);
        if ($this->request->isPost()) {
            $form->loadData($this->request->getBody());
            if ($form->validate() === true) {
                Session::setFlash('msg', 'A new team has been added', [
                    'type' => Session::FLASH_TYPE_SUCCESS,
                    'title' => 'Teams',
                    'dismissable' => true
                ]);
            }
        }
        $player = new PlayerModel();
        $platform = new AssetPlatformModel();
        $players = [];
        $platforms = [];
        $team_types = [];
        foreach ($player->getAll() as $player) {
            $players[$player['PlayerID']] = $player['PlayerIGN'];
        }
        foreach ($platform->getAll() as $platform) {
            $platforms[$platform['AssetPlatformID']] = $platform['AssetPlatformName'];
        }
        foreach (array_reverse($this->model->getTeamTypes()) as $team_type) {
            $team_types[$team_type['TeamTypeID']] = $team_type['TeamTypeName'];
        }
        $this->render('teams/new.html.twig', [
            'title' => 'New Team',
            'form' => [
                'form' => $form,
                'players' => $players,
                'platforms' => $platforms,
                'team_types' => $team_types
            ]
        ]);
    }

    public function manageSelectTeamAction()
    {
        $teams = $this->model->getPendingTeams();
        $this->render('teams/manage_select.html.twig', [
            'title' => 'Teams | Manage Team',
            'teams' => $teams
        ]);
    }

    public function manageSelectManagersAction()
    {
        $teams = $this->model->getPendingTeams();
        $this->render('teams/manage_select.html.twig', [
            'title' => 'Teams | Manage Team',
            'teams' => $teams
        ]);
    }

    public function manageAddAction()
    {
        if (has_key_presence('slug', $this->params)) {
            $slug = $this->params['slug'];
            if (valid_slug($slug)) {
                $team = $this->model->getTeamBySlug($slug);
                $this->render('teams/manage_add.html.twig', [
                    'title' => 'Teams | Manage Team',
                    'team' => $team
                ]);
            }
        }
    }
}