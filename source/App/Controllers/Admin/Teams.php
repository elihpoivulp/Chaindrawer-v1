<?php

namespace CD\App\Controllers\Admin;

use CD\Config\Config;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\Teams as TeamsModel;

class Teams extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$template_namespace, static::$template_namespace);
        $this->loadModel('Teams');
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
        if (has_key_presence('manage', $_GET)) {
            $this->manageSelectManagersAction(true, $_GET['manage']);
        } else {
            $errors = [];
            $vals = [];
            $type = 1;
            if ($this->request->isPost()) {
                $cleaned = [];
                $b = $this->request->getBody();
                $expected = [
                    'team_name' => [
                        'min' => 4,
                        'max' => 50
                    ],
                    'team_value' => [
                        'min' => 8,
                        'max' => 22
                    ],
                    'team_type' => [
                        'min' => 1,
                        'max' => 4
                    ],
                    'tracker_address' => [
                        'max' => 200
                    ],
                    'date_established' => [
                        'min' => 10,
                        'max' => 10
                    ],
                ];
                foreach ($expected as $key => $rules) {
                    $value = $b[$key];
                    $field = 'Field' . ' "' . ucwords(str_replace('_', ' ', $key)) . '" ';
                    if (!has_inclusion_of($key, $b) && !$value) {
                        $errors[] = $field . 'cannot be empty.';
                    } else {
                        if ($rules && is_array($rules)) {
                            foreach ($rules as $rule => $rule_val) {
                                switch ($rule) {
                                    case 'max':
                                        if (has_length_greater_than($value, $rule_val)) {
                                            $errors[$key] = $field . 'can\'t have characters greater than ' . $rule_val;
                                        }
                                        break;
                                    case 'min':
                                        if (has_length_less_than($value, $rule_val)) {
                                            $errors[$key] = $field . 'can\'t have characters less than  ' . $rule_val;
                                        }
                                        break;
                                }
                            }
                        }
                        if ($key === 'team_name') {
                            if ($this->model->teamNameTaken(slugify($value))) {
                                $errors[$key] = 'Team with the same name already exists';
                            }
                        }
                        if ($key === 'team_type') {
                            if (!preg_match('/^\d+$/', $value)) {
                                $errors[$key] = $field . 'does not contain a valid integer/number';
                            }
                        }
                        if ($key === 'date_established') {
                            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                $errors[$key] = $field . 'does not seem to be a valid date format';
                            }
                        }
                        if ($key === 'team_value') {
                            if (!preg_match('/^[.,\d]+$/', $value)) {
                                $errors[$key] = $field . 'does not seem to be a valid money format';
                            }
                        }
                        if ($key === 'tracker_address') {
                            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                                $errors[$key] = $field . 'does not seem to be a valid URL';
                            }
                        }
                        $vals[$key] = $value;
                        $cleaned[$key] = strip_tags($value);
                    }
                }
                if (!$errors) {
                    try {
                        $cleaned['slug'] = slugify($cleaned['team_name']);
                        $cleaned['team_value'] = str_replace(',', '', $cleaned['team_value']);
                        $this->model->saveNewTeam($cleaned);
                        Response::redirect('admin/teams/new?manage=' . urlencode($cleaned['slug']));
                    } catch (\Exception | \PDOException $e) {
                        Session::setFlash('toastr', 'An error has occurred while saving data. Please try again later.', [
                            'type' => Session::FLASH_TYPE_WARNING,
                            'title' => 'Failed',
                            'dismissable' => true
                        ]);
                    }
                }
            }
            $this->render('teams/new.html.twig', [
                'title' => 'New Axie Team',
                'errors' => $errors,
                'vals' => $vals,
                'team_types' => $this->model->getTeamTypes(),
                'd_type' => $type
            ]);
        }
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
                        $label = $manager->UserFullName;
                        if ($this->user->getUserID() == intval($manager->UserID)) {
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
                        'team' => $team,
                        'account' => $this->user,
//                        'last_payout' => $team->getLastPayoutDate(),
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

    public function manageSelectTeamAction()
    {
        $this->render('teams/manage_select.html.twig', [
            'title' => 'Teams | Manage Team',
        ]);
    }

    public function manageSelectManagersAction($step_2 = false, $slug = null)
    {
        if (!$step_2) {
            $slug = $this->params['slug'] ?? null;
        }
        $slug = urldecode($slug);
        if (valid_slug($slug) && $team = $this->model->getTeamBySlug($slug)) {
            $this->render('teams/manage_add.html.twig', [
                'title' => 'Teams | Manage Team',
                'team' => $team,
                'step_2' => $step_2,
            ]);
        } else {
            echo 'die';
        }
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

    public function getTeams()
    {
        echo json_encode($this->model->getTeamBySearch($_GET['search'], $_GET['skip'] ?? [0]));
    }
}