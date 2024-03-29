<?php

namespace CD\Core;

use CD\Core\Sessions\Session;
use CD\Core\Sessions\SessionsUserAuth;
use CD\Models\ActiveAccount;
use CD\Models\User;
use Exception;

class LoginRequiredController extends Controller
{
    /**
     * @throws Exception
     */
    protected User $user;

    protected function before(): bool
    {
        if (!SessionsUserAuth::isLoggedIn()) {
            Session::setFlash('toastr', 'You must login first',
                [
                    'dismissable' => true,
                    'title' => 'Alert',
                    'type' => 'warning',
                    'timeout' => '5000',
                ]
            );
            $next = '';
            if ($uri = $this->params['requested_uri']) {
                $next = '?next=' . urlencode($uri);
            }
            SessionsUserAuth::logout();
            Response::redirect('auth/login' . $next);
        }
        $login = new ActiveAccount();
        $user = $login->getRelatedUser(SessionsUserAuth::getToken());
        $this->user = $user;
        return true;
    }
}