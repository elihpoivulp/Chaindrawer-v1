<?php

namespace CD\App\Controllers\Admin;

use CD\Core\AbstractRoute;

class Routes extends AbstractRoute
{
    public ?string $controller = 'Admin';
    public ?array $routes = [
        '' => []
    ];
}