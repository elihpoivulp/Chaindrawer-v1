<?php

namespace CD\Core\ViewControllers;

use CD\App\Controllers\Auth\Auth;
use CD\App\Controllers\Redirect;
use CD\Core\LoginRequiredController;
use CD\Core\Response;

class ManagerViewOnly extends BaseRestrictedViewController
{
    public function acceptedRole(): string
    {
        return 'manager';
    }
}