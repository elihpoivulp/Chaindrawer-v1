<?php

namespace CD\App\Controllers\Auth;

use CD\Core\AbstractRoute;

class Routes extends AbstractRoute
{
    public ?string $controller = 'Auth';
    public ?array $routes = [
        '' => ['action' => ''],
        'login' => ['action' => ''],
        'logout' => ['action' => 'logout'],
        'redirect' => ['action' => 'redirect']
    ];
}