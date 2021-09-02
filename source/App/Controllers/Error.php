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
        if (isset($_GET['c'])|| (isset($_GET['c']) && intval($_GET['c']) === 403)) {
            $code = 403;
            if (isset($_GET['r'])) {
                $message = Request::getIPAddress() . ' is trying to access resource folder: ' . $_GET['r'];
            } else {
                $message = 'An error has occurred. Please try again later';
            }
        }
        throw new Exception($message, $code);
    }
}