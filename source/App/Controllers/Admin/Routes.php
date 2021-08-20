<?php

namespace CD\App\Controllers\Admin;

use CD\Core\AbstractRoute;

class Routes extends AbstractRoute
{
    public ?string $controller = 'Admin';
    public ?array $routes = [
        '' => [],
        'teams' => ['action' => 'index', 'controller' => 'Teams'],
        'teams/new' => ['action' => 'newTeam', 'controller' => 'Teams'],
    ];
}