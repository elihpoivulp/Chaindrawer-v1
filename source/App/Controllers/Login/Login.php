<?php

namespace CD\App\Controllers\Login;

use CD\App\Controllers\Login\Forms\AuthForm;
use CD\Core\Forms\FormController;
use CD\Core\Forms\ModelForm;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\Sessions\Session;
use CD\Core\Sessions\SessionsUserAuth;
use CD\Core\View;

class Login extends FormController
{
    static protected string $template_namespace = 'home';

    protected function form(): ModelForm
    {
        return new AuthForm($this->model);
    }

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . '/' . static::$template_namespace, static::$template_namespace);
        // $this->loadModel(AuthenticationModel::class);
        $this->loadModel('AuthenticationModel');
    }

    public function indexAction(): void
    {
        $user = new SessionsUserAuth();
        if ($user->user()) {
            Response::redirect('admin');
        }
        $form = $this->form();
        if ($this->request->isPost()) {
            $form->loadData($this->request->getBody());
            if ($form->validate() === true) {
                Response::redirect('redirect');
            } else {
                sleep(2);
                Session::setFlash('msg', 'Incorrect password/username combination.', [
                    'type' => Session::FLASH_TYPE_WARNING,
                    'title' => 'Login unsuccessful',
                    'dismissable' => true
                ]);
            }
        }
        $this->render('login.html.twig', [
            'title' => 'Login',
            'form' => $form
        ]);
    }

    public function logoutAction()
    {
        $user = new SessionsUserAuth();
        if ($user->isLoggedIn()) {
            $user->logout();
        }
        Session::setFlash('toastr', 'You\'ve been logged out.', [
        'type' => Session::FLASH_TYPE_SUCCESS,
        'title' => 'Success',
        'dismissable' => true
    ]);
        Response::redirect('login');
    }
}