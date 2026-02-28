<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Config\Repository;

if (! class_exists(Container::class) || ! class_exists(Repository::class)) {
    return;
}

$container = Container::getInstance();

if (! $container instanceof Container) {
    $container = new Container();
    Container::setInstance($container);
}

if (! $container->bound('config')) {
    $defaults = require __DIR__ . '/config/campaign-kit.php';
    $container->instance('config', new Repository([
        'campaign-kit' => $defaults,
    ]));
}
