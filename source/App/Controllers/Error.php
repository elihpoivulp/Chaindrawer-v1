<?php

namespace CD\App\Controllers;

use CD\Core\Controller;
use CD\Core\Request;
use Exception;

class Error extends Controller
{
    /**
     * @throws Exception
     */
    public function indexAction()
    {
        $code = 404;
        $message = 'Page does not exist.';
        if (isset($_GET['c'])|| (isset($_GET['c']) && intval($_GET['c']) === 403) && isset($_GET['r'])) {
            $code = 403;
            $message = Request::getIPAddress() . ' is trying to access resource folder: ' . $_GET['r'];
        }
        throw new Exception($message, $code);
    }
}