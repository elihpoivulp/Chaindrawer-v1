<?php
/**
 * Front controller
 */


define('BASE_PATH', dirname(__DIR__));
require_once  BASE_PATH . '/source/bootstrap.php';


use CD\Core\Request;
use CD\Core\Router;
use CD\Core\View;

error_reporting(E_ALL);
set_error_handler('\CD\Core\Error::errorHandler');
set_exception_handler('\CD\Core\Error::exceptionHandler');

$request = new Request();
$view = new View();

$router = new Router();
$router->addRoute('manager', [], '@manager/routes');
$router->addRoute('admin', [], '@admin/routes');
$router->addRoute('auth', [], '@auth/routes');
$router->addRoute('sandbox', ['controller' => 'Sandbox']);
// $router->addRoute('login', ['controller' => 'Login', 'namespace' => 'Login']);
// $router->addRoute('logout', ['controller' => 'Login', 'action' => 'logout', 'namespace' => 'Login']);
$router->dispatch($request, $view);
