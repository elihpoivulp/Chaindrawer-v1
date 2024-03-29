<?php

namespace CD\App\Controllers\Auth;

use CD\App\Controllers\Auth\Forms\AuthForm;
use CD\Config\Config;
use CD\Core\CSRFToken;
use CD\Core\Forms\FormController;
use CD\Core\Forms\ModelForm;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\Sessions\Session;
use CD\Core\Sessions\SessionsUserAuth;
use CD\Core\View;
use CD\Models\ActiveAccount;
use Exception;

class Auth extends FormController
{
    static protected string $template_namespace = 'home';
    public const ROLE_ROUTES = [
        'manager' => 'manager',
        'admin' => 'admin'
    ];

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

    /**
     * @throws Exception
     */
    public function indexAction(): void
    {
        if (SessionsUserAuth::isLoggedIn()) {
            $this->redirect(SessionsUserAuth::getToken());
        }
        $form = $this->form();
        if ($this->request->isPost()) {
            $form->loadData($this->request->getBody());
            if ($form->validate() === true) {
                $this->redirect(SessionsUserAuth::getToken());
            } else {
                sleep(2);
                Session::setFlash('msg', 'Incorrect password/username combination.', [
                    'type' => Session::FLASH_TYPE_WARNING,
                    'title' => 'Login unsuccessful',
                    'dismissable' => true
                ]);
            }
        }
        $next = isset($_GET['next']) ? '?next=' . urldecode($_GET['next']) : '';
        $this->render('login.html.twig', [
            'title' => 'Login',
            'form' => $form,
            'next' => $next,
            '_token' => CSRFToken::generateToken()
        ]);
    }

    /**
     * @throws Exception
     */
    public function logoutAction()
    {
        if (SessionsUserAuth::isLoggedIn()) {
            Session::setFlash('toastr', 'You\'ve been logged out.', [
                'type' => Session::FLASH_TYPE_SUCCESS,
                'title' => 'Success',
                'dismissable' => true
            ]);
            SessionsUserAuth::logout();
            Response::redirect('auth/login');
        }
        Response::errorPage(404);
    }

    /**
     * @throws Exception
     */
    public function redirect(string $token): void
    {
        if (SessionsUserAuth::getToken() === $token) {
            $login = new ActiveAccount();
            $user = $login->getRelatedUser($token);
            $role = $user->getRoles()[0] ?? null;
            if (!is_null($role) && has_key_presence(strtolower($role['RoleName']), self::ROLE_ROUTES)) {
                $role = strtolower($role['RoleName']);
                $next = isset($_GET['next']) ? urldecode($_GET['next']) : self::ROLE_ROUTES[strtolower($role)];
                Response::redirect($next);
            } else {
                Session::setFlash('toastr', 'An error has occurred. Please try again later.', [
                    'type' => Session::FLASH_TYPE_WARNING,
                    'title' => 'Warning',
                    'dismissable' => true
                ]);
                SessionsUserAuth::logout();
                Response::redirect('auth/login');
            }
        }
        Response::redirect('auth/login');
    }
}