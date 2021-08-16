<?php

use CD\Core\Sessions\Session;

ob_start();
Session::start();
error_reporting(E_ALL);