<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewTeamForm;
use CD\Core\Request;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\AssetPlatformModel;
use CD\Models\PlayerModel;
use CD\Models\TeamModel;

class Teams extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
    }

    public function indexAction()
    {
        $this->render('teams/all.html.twig', [
            'title' => 'Teams'
        ]);
    }

    public function newTeamAction()
    {
        $model = new TeamModel();
        $form = new NewTeamForm($model);
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
        foreach (array_reverse($model->getTeamTypes()) as $team_type) {
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
}