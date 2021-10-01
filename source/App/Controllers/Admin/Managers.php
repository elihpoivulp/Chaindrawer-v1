<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewManagerForm;
use CD\Core\Request;
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
//        $form = new NewManagerForm(new Manager());
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
                            $errors[$uk] = $field. 'does not contain a valid email address';
                        }
                    }
                    if ($key === 'Gender' && $value && has_length_exactly($value, 1)) {
                        $gender = $value;
                    }
                    if ($key === 'Phone') {
                        if (!preg_match('/[+\d]{11,13}/', $value)) {
                            $errors[$uk] = $field . 'does not contain a valid phone number';
                        }
                    }
                    $vals[$uk] = $value;
                    $cleaned[':' . $uk] = htmlspecialchars(strip_tags($value));
                }
            }
            if (has_inclusion_of('UserMiddleName', $b)) {
                $cleaned[':UserMiddleName'] = htmlspecialchars(strip_tags($b['UserMiddleName']));
            }
            if (!$errors) {
                try {
                    $user->saveNewUser($cleaned);
                    Session::setFlash('msg', 'A new user has been added!', [
                        'type' => Session::FLASH_TYPE_SUCCESS,
                        'title' => 'Login unsuccessful',
                        'dismissable' => true
                    ]);
                } catch (\Exception | \PDOException $e) {
                    throw new \Exception($e->getMessage());
                    Session::setFlash('toastr', 'An error has occurred while saving data. Please try again later.', [
                        'type' => Session::FLASH_TYPE_WARNING,
                        'title' => 'Success',
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