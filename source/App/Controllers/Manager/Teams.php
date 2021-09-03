<?php

namespace CD\App\Controllers\Manager;

use CD\Config\Config;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use CD\Models\Teams as TeamsModel;

class Teams extends ManagerViewOnly
{
    static protected string $template_namespace = 'manager';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$template_namespace, static::$template_namespace);
    }

    public function indexAction()
    {

        $my_teams = $this->account->manager->getMyTeams();
        // echo '<pre>';
        // print_r($my_teams[0]->AxieTeam());
        // echo '</pre>';
        // exit;
        $this->render('teams/index.html.twig', [
            'title' => 'My Teams',
            'account' => $this->account,
            'my_teams' => $my_teams,
            'menu' => [
                'menu_page' => $this->getURIOnPos(1),
                'menu_active' => 'my_teams'
            ]
        ]);
    }

    public function viewAction()
    {
        if (has_key_presence('slug', $this->params)) {
            $slug = $this->params['slug'];
            if (valid_slug($slug)) {
                $teams = new TeamsModel();
                $team = $teams->getTeamBySlug($slug);
                if ($team) {
                    $labels = [];
                    $rates = [];
                    $colors = [];
                    foreach ($managers = $team->getTeamManagers() as $manager) {
                        // $labels[] = 'INV-' . strtoupper(substr(md5($manager->getManagerAccountID()), 0, 5));
                        $label = $manager->getManagerAccountID();
                        if ($this->account->getUserID() == intval($manager->UserID)) {
                            $label = 'Me';
                        }
                        $labels[] = $label;
                        $rates[] = $manager->OwnershipRate;
                        while (true) {
                            $color = array_rand(array_flip(Config::CHART_COLORS), 1);
                            if (!in_array($color, $colors)) {
                                $colors[] = $color;
                                break;
                            }
                        }
                    }
                    $slp_grind = [
                        'labels' => [],
                        'data' => []
                    ];
                    foreach ($daily_grind = $team->AxieTeam()->getDailySLPGrinds() as $grind) {
                        $slp_grind['labels'][] = date_format(date_create($grind['DailySLPGrindDateAdded']), 'D');
                        $slp_grind['data'][] = $grind['DailySLPGrindAmount'];
                    }
                    $this->render('teams/view.html.twig', [
                        'title' => $team->AssetTeamName,
                        'account' => $this->account,
                        'team' => $team,
                        'charts' => [
                            'doughnut' => [
                                'has_data' => $managers,
                                'labels' => implode(', ', $labels),
                                'rates' => implode(', ', $rates),
                                'colors' => implode(', ', $colors)
                            ],
                            'line' => [
                                'has_data' => $daily_grind,
                                'labels' => implode(', ', $slp_grind['labels']),
                                'data' => implode(', ', $slp_grind['data']),
                            ]
                        ]
                    ]);
                } else {
                    Response::errorPage(404);
                }
            }
        }
    }

    public function earningsAction()
    {
        $this->render('teams/earnings.html.twig', [
            'title' => 'Team Earnings',
            'account' => $this->account,
            'payouts' => $this->account->manager->getPayouts()
        ]);
    }

    public function earningsDetailsAction()
    {
        $team_payout_details = $this->account->manager->getPayout($this->params['id']);
        if ($team_payout_details) {
            $teams = new TeamsModel();
            $team = $teams->getTeamBySlug($team_payout_details['AssetTeamSlug'])->getTeamManagers();
            $this->render('teams/details.html.twig', [
                'title' => 'Earnings Details',
                'account' => $this->account,
                'p' => $team_payout_details,
                'm' => $team
            ]);
        } else {
            Response::errorPage(404);
        }
    }

    public function redirect()
    {
        Response::redirect('manager/teams');
    }
}