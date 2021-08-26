<?php

namespace CD\Core\ViewControllers;

use CD\App\Controllers\Auth\Auth;
use CD\Core\LoginRequiredController;
use CD\Core\Response;

abstract class BaseRestrictedViewController extends LoginRequiredController
{
    abstract function acceptedRole(): string;

    protected function before(): bool
    {
        parent::before();
        if ($this->user) {
            $user = $this->user;
            $roles = $user->getRoles();
            $has_perm = false;
            $_ = 0;
            while (!$has_perm) {
                $u_role = strtolower($roles[$_]['RoleName']);
                if ($u_role === $this->acceptedRole()) {
                    $has_perm = true;
                }
                if ($_ > count($roles)) {
                    break;
                }
                $_++;
            }
            if (!$has_perm) {
                Response::redirect('auth/login');
            }
            return true;
        }
        return false;

        // $user = parent::before();
        // $roles = $user->getRoles();
        // $role = strtolower($user->getRole());
        // if ($role !== $this->acceptedRole()) {
        //     if (has_key_presence($role, Auth::ROLE_ROUTES)) {
        //         Response::redirect(Auth::ROLE_ROUTES[$role]);
        //     }
        // }
        // return true;
    }
}