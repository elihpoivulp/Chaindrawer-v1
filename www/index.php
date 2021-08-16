<?php
/**
 * Front controller
 */


define('BASE_PATH', dirname(__DIR__));
require_once  BASE_PATH . '/source/bootstrap.php';


use CD\Core\Request;
use CD\Core\Router;
use CD\Core\View;

$request = new Request();
$view = new View();

$router = new Router();
$router->addRoute('manager', routes_file: '@manager/routes');
$router->addRoute('admin', routes_file: '@admin/routes');
$router->addRoute('auth', routes_file: '@auth/routes');
$router->addRoute('sandbox', ['controller' => 'Sandbox']);
// $router->addRoute('login', ['controller' => 'Login', 'namespace' => 'Login']);
// $router->addRoute('logout', ['controller' => 'Login', 'action' => 'logout', 'namespace' => 'Login']);
$router->dispatch($request, $view);
