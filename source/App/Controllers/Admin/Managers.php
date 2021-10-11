<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewManagerForm;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\Manager;

class Managers extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$template_namespace, static::$template_namespace);
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
        if (has_key_presence('manage', $_GET)) {
            $this->includeToTeams(true, $_GET['manage']);
        } else {
            $errors = [];
            $vals = [];
            $gender = 'M';
            if ($this->request->isPost()) {
                $user = new \CD\Models\Users();
                $b = $this->request->getBody();
                $cleaned = [];
                $expected = [
                    'First-Name' => [
                        'max' => 50,
                        'min' => 2
                    ],
                    'Last-Name' => [
                        'max' => 50,
                        'min' => 2
                    ],
                    'Gender' => [
                        'max' => 1,
                        'min' => 1
                    ],
                    'Email' => [
                        'max' => 50,
                        'min' => 8,
                    ],
                    'Address' => [
                        'max' => 60,
                        'min' => 8
                    ],
                    'Phone' => [
                        'max' => 13,
                        'min' => 11
                    ]
                ];
                foreach ($expected as $key => $rules) {
                    $uk = 'User' . str_replace('-', '', $key);
                    $value = $b[$uk];
                    $field = 'Field' . ' "' . ucwords(str_replace('-', ' ', $key)) . '" ';
                    if (!has_inclusion_of($uk, $b) && !$value) {
                        $errors[] = $field . 'cannot be empty.';
                    } else {
                        if ($rules && is_array($rules)) {
                            foreach ($rules as $rule => $rule_val) {
                                switch ($rule) {
                                    case 'max':
                                        if (has_length_greater_than($value, $rule_val)) {
                                            $errors[$uk] = $field . 'can\'t have characters greater than ' . $rule_val;
                                        }
                                        break;
                                    case 'min':
                                        if (has_length_less_than($value, $rule_val)) {
                                            $errors[$uk] = $field . 'can\'t have characters less than  ' . $rule_val;
                                        }
                                        break;
                                }
                            }
                        }
                        if ($key === 'Email') {
                            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                if (!$user->isEmailUnique($value)) {
                                    $errors[$uk] = 'Email is already taken';
                                }
                            } else {
                                $errors[$uk] = $field . 'does not contain a valid email address';
                            }
                        }
                        if ($key === 'Gender' && $value && has_length_exactly($value, 1)) {
                            $gender = $value;
                        }
                        if ($key === 'Phone') {
                            if (!preg_match('/^[+\d]{11,13}$/', $value)) {
                                $errors[$uk] = $field . 'does not contain a valid phone number';
                            }
                        }
                        $vals[$uk] = $value;
                        $cleaned[':' . $uk] = strip_tags($value);
                    }
                }
                if (has_inclusion_of('UserMiddleName', $b)) {
                    $cleaned[':UserMiddleName'] = strip_tags($b['UserMiddleName']);
                }
                if (!$errors) {
                    try {
                        $id = $user->saveNewUser($cleaned);
                        Response::redirect('admin/managers/new?manage=' . urlencode($id));
                    } catch (\Exception | \PDOException $e) {
                        Session::setFlash('toastr', 'An error has occurred while saving data. Please try again later.', [
                            'type' => Session::FLASH_TYPE_WARNING,
                            'title' => 'Failed',
                            'dismissable' => true
                        ]);
                    }
                }
            }
            $this->render('managers/new.html.twig', [
                'title' => 'New Manager',
                'errors' => $errors,
                'vals' => $vals,
                'gender' => $gender
            ]);
        }
    }


    public function includeToTeams($step_2 = false, $id = null)
    {
        if ($this->request->isPost() && has_key_presence('manage', $_GET)) {
            if (has_key_presence('teams', $_POST)) {
                $data['teams'] = $_POST['teams'];
                if (!is_array($data['teams']) || !preg_match('/\d+/', $_GET['manage'])) {
                    Response::errorPage(500);
                }
                $this->params['id'] = $_GET['manage'];
                $this->setAmount(true, $_POST['teams']);
                exit;
            } else {
                Session::setFlash('msg', 'Please select a team.', [
                    'type' => Session::FLASH_TYPE_WARNING,
                    'dismissable' => true
                ]);
            }
        }
        if (!$step_2) {
            $id = $this->params['id'] ?? null;
        }
        $id = urldecode($id);
        $users = new \CD\Models\Users();
        $user = $users->getOne($id, 'UserID');
        $teams = new \CD\Models\Teams();
        if (preg_match('/\d+/', $id) && $user) {
            $this->render('managers/select_teams.html.twig', [
                'title' => 'New Manager | Add to Teams',
                'manager' => $user,
                'available_teams' => $teams->getTeamByStatus(0),
                'step_2' => $step_2
            ]);
        } else {
            Response::errorPage(400);
        }
    }

    public function setAmount($step_2 = false, $ids = [])
    {
        $errors = [];
        $vals = [];
        $_ids = [];
        $teams = new \CD\Models\Teams();
        $users = new \CD\Models\Users();
        if (!$this->params['id'] || !preg_match('/\d+/', $this->params['id']) || !$this->request->isPost() || !has_key_presence('teams', $_POST)) {
            Response::errorPage(404);
        }
        if ($this->request->isPost() && !$step_2 && has_key_presence('teams', $_POST)) {
            if (has_key_presence('step_2', $_POST)) {
                $step_2 = $_POST['step_2'];
            }
            if ($_POST['teams']) {
                $fail_count = 0;
                foreach ($_POST['teams'] as $team_name => $data) {
                    $a = $data['amount'];
                    $n = $data['name'];
                    $i = $data['id'];
                    if (!preg_match('/^[.,\d]+$/', $a)) {
                        $errors[$team_name] = 'Field ' . $n . ' does not contain a valid money format';
                    }
                    if (has_length_greater_than($a, 22)) {
                        $errors[$team_name] = $n . ' can\'t have characters greater than 22';
                    } else if (has_length_less_than($a, 8)) {
                        $errors[$team_name] = $n . ' can\'t have characters less than 8';
                    }
                    if (!$errors[$team_name]) {
                        $team = null;
                        $amt = str_replace(',', '', $a);
                        $team = $teams->getTeamByID($i);
                        $max = $team->AssetTeamValue - $team->AssetTeamCollectedAmount;
                        if ($amt > $max) {
                            $errors[$team_name] = $n . '\'s amount cannot be greater<br>than ₱ ' . number_format($max, 2);
                        }
                    }
                    if (!$errors[$team_name]) {
                        try {
                            $user = $users->getOne($this->params['id'], 'UserID');
                            $team->newShare($amt, $user->getManagerAccount()->getManagerAccountID());
                        } catch (\Exception | \PDOException $e) {
                            $fail_count += 1;
                        }
                    } else {
                        $vals[$team_name] = $a;
                        $_ids[] = $i;
                    }
                }
                if (!$errors) {
                    if (!$fail_count) {
                        Session::setFlash('toastr', 'A new manager has been added!', [
                            'type' => Session::FLASH_TYPE_SUCCESS,
                            'title' => 'Success',
                            'dismissable' => true
                        ]);
                    } else {
                        Session::setFlash('toastr', 'An error has occurred. Please try again later.', [
                            'type' => Session::FLASH_TYPE_WARNING,
                            'title' => 'Warning',
                            'dismissable' => true
                        ]);
                    }
                    Response::redirect('admin/managers/new');
                }
            }
        }
        if (!$ids) {
            $ids = $_ids;
        }
        $id = urldecode($this->params['id']);
        $users = new \CD\Models\Users();
        $user = $users->getOne($id, 'UserID');
        $teams = $teams->getTeamsByIDs($ids);
        if (preg_match('/\d+/', $id) && $user && $teams) {
            $this->render('managers/set_amount.html.twig', [
                'title' => 'New Manager | Add to Teams',
                'manager' => $user,
                'teams' => $teams,
                'step_2' => $step_2,
                'errors' => $errors,
                'vals' => $vals,
            ]);
        } else {
            Response::errorPage(400);
        }
    }
}