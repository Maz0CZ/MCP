<?php

declare(strict_types=1);

namespace App;

use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList();
        $router->addRoute('api/servers/<id>/console', 'Server:console');
        $router->addRoute('api/servers/<id>/command', 'Server:command');
        $router->addRoute('api/servers/<id>/action', 'Server:action');
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }
}
