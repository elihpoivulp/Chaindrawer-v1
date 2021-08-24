<?php

namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Admin\Forms\NewUserForm;
use CD\Core\Request;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;
use CD\Models\Users as UserModel;
use CD\Models\User;

class Users extends AdminViewOnly
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
        $users = new UserModel();
        $users = $users->getAllUsers();
        // echo '<pre>';
        // print_r($users[0]->getLogin());
        // echo '</pre>';
        // exit;
        $this->render('users/all.html.twig', [
            'title' => 'Users',
            'users' => $users
        ]);
    }

    public function newAction()
    {
        $form = new NewUserForm(new User());
        if ($this->request->isPost()) {
            $form->loadData($this->request->getBody());
            if ($form->validate() === true) {
                Session::setFlash('msg', 'A new user has been added', [
                    'type' => Session::FLASH_TYPE_SUCCESS,
                    'title' => 'Success!',
                    'dismissable' => true
                ]);
            } else {
                if (!$form->errors) {
                    Session::setFlash('msg', 'An error has occurred. Please try again later', [
                        'type' => Session::FLASH_TYPE_WARNING,
                        'title' => 'Error',
                        'dismissable' => true
                    ]);
                }
            }
        }
        $this->render('users/new.html.twig', [
            'title' => 'New User',
            'form' => [
                'form' => $form,
                'genders' => [
                    'M' => 'Male',
                    'F'  => 'Female'
                ]
            ]
        ]);
    }
}