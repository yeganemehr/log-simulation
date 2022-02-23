<?php

namespace Yeganemehr\LogSimulation;

use Symfony\Component\Console\Application;

if (is_dir(__DIR__.'/../vendor')) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
    require_once __DIR__.'/../autoload.php';
}

$app = new Application();

$app->add(new Commands\Start());

return $app;
