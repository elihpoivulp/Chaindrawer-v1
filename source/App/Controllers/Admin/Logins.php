<?php

namespace CD\App\Controllers\Admin;

use CD\Core\Forms\Form;
use CD\Core\Request;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;

class Logins extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
        $this->loadModel('Teams');
    }

    public function newAction()
    {
        $data = $this->request->getBody();
        $users = new \CD\Models\Users();
        $users_selection = [];
        foreach ($users->getAllUsers() as $user) {
            for ($i = 0; $i <= 10; $i++) {
                $users_selection[$user->getUserID() + $i] = $user->getUserName();
            }
        }
        $template = '';
        $form = new Form();
        if (has_key_presence('ref', $data) && $data['ref'] === 'modal') {
            if (has_key_presence('userid', $data) && preg_match('/\d+/', $data['userid'])) {
                $form->setInitial('UserID', $data['userid']);
                $template = 'logins/new-modal.html.twig';
            } else {
                echo '<strong class="text-danger">Invalid request</strong>';
            }
        }
        $this->render($template, [
            'form' => [
                'form' => $form,
                'users' => $users_selection
            ]
        ]);
    }
}