<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewTeamForm;
use CD\Core\Request;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\AssetPlatforms;
use CD\Models\Players;
use CD\Models\Team;

class Teams extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
        $this->loadModel('Teams');
    }

    public function indexAction()
    {
        $teams = $this->model->getTeams();
//        echo '<pre>';
//        print_r($teams);
//        echo '</pre>';
//        exit;
        $this->render('teams/all.html.twig', [
            'title' => 'Teams',
            'teams' => $teams
        ]);
    }

    public function newTeamAction()
    {
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
                    $cleaned[$key] = htmlspecialchars(strip_tags($value));
                }
            }
            if (!$errors) {
                try {
                    $cleaned['slug'] = slugify($cleaned['team_name']);
                    $cleaned['team_value'] = str_replace(',', '', $cleaned['team_value']);
                    $this->model->saveNewTeam($cleaned);
                    Session::setFlash('msg', 'New team created successfully!', [
                        'type' => Session::FLASH_TYPE_SUCCESS,
                        'title' => 'Success!',
                        'dismissable' => true
                    ]);
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