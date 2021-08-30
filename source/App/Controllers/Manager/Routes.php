<?php

namespace CD\App\Controllers\Manager;

use CD\Core\AbstractRoute;

class Routes extends AbstractRoute
{
    public ?string $controller = 'Manager';
    public ?array $routes = [
        'dashboard' => ['action' => 'index'],
        'teams' => ['controller' => 'Teams'],
        'teams/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}' => ['action' => 'view', 'controller' => 'Teams'],
        'payouts' => ['controller' => 'Payouts'],
        'payouts/{id:\d+}' => ['action' => 'details', 'controller' => 'Payouts'],
    ];
}