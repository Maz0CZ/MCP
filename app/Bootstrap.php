<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $configurator->setTimeZone('UTC');
        $configurator->enableTracy(__DIR__ . '/../log');
        $configurator->setTempDirectory(__DIR__ . '/../temp');
        $configurator->addStaticParameters([
            'appDir' => __DIR__,
            'wwwDir' => __DIR__ . '/../www',
        ]);
        $configurator->addConfig(__DIR__ . '/config/common.neon');

        return $configurator;
    }
}
