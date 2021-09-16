<?php

use CD\Core\Sessions\Session;

ob_start();
Session::start();
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
set_error_handler('\CD\Core\Error::errorHandler');
set_exception_handler('\CD\Core\Error::exceptionHandler');