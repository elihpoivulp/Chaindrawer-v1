<?php

namespace CD\App\Controllers;

use CD\Core\LoginRequiredController;

class Redirect extends LoginRequiredController
{
    public function indexAction()
    {
        echo 'nice';
    }
}