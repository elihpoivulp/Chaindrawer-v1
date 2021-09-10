<?php

namespace CD\App\Controllers\Manager;

use CD\Core\AbstractRoute;

class Routes extends AbstractRoute
{
    // public ?string $controller = 'Manager';
    public ?string $controller = 'Teams';
    public ?array $routes = [
        // 'dashboard' => ['action' => 'index'],
        '' => ['action' => 'redirect'],
        'withdraw' => ['controller' => 'Withdrawals'],
        'withdraw/process' => ['controller' => 'Withdrawals', 'action' => 'process'],
        'teams' => ['controller' => 'Teams'],
        'teams/earnings' => ['action' => 'earnings'],
        'teams/earnings/{id:\d+}' => ['action' => 'earningsDetails'],
        'teams/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}' => ['action' => 'view'],
        'transactions' => ['controller' => 'Transactions'],
        'transactions/deposits' => ['controller' => 'Transactions'],
        'transactions/{id:\d+}' => ['action' => 'details', 'controller' => 'Transactions'],
    ];
}