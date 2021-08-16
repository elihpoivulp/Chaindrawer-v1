<?php

namespace CD\App\Controllers\Manager;

use CD\Core\AbstractRoute;

class Routes extends AbstractRoute
{
    public ?string $controller = 'Manager';
    public ?array $routes = [
        'dashboard' => ['action' => 'index'],
        // 'profile' => ['action' => 'profile'],
        'teams' => ['action' => 'index', 'controller' => 'Teams']
    ];
}