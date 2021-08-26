<?php

namespace CD\Core\ViewControllers;

use CD\App\Controllers\Auth\Auth;
use CD\App\Controllers\Redirect;
use CD\Core\LoginRequiredController;
use CD\Core\Response;
use CD\Models\User;

class ManagerViewOnly extends BaseRestrictedViewController
{
    protected User $account;
    public function acceptedRole(): string
    {
        return 'manager';
    }

    protected function before(): bool
    {
        parent::before();
        if ($this->user->manager) {
            $this->account = $this->user;
            return true;
        } else {
            exit('This user has a Manager role but has no Manager Account');
        }
    }
}