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
        'teams/manage/' => ['action' => 'manageSelectTeam', 'controller' => 'Teams'],
        'teams/manage/add' => ['action' => 'manageSelectManagers', 'controller' => 'Teams'],
        'teams/manage/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}' => ['action' => 'manageAdd', 'controller' => 'Teams'],
        'managers' => ['controller' => 'Managers'],
        'managers/new' => ['action' => 'new', 'controller' => 'Managers'],
        'users' => ['controller' => 'Users'],
        'users/new' => ['action' => 'new', 'controller' => 'Users'],
        'logins' => ['controller' => 'Logins'],
        'logins/new' => ['action' => 'new', 'controller' => 'Logins'],
        'withdrawals/get-fragment' => ['action' => 'getWithdrawalDetails', 'controller' => 'Withdrawals'],
        'withdrawals/process' => ['action' => 'process', 'controller' => 'Withdrawals'],
    ];
}