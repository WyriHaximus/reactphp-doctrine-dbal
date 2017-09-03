<?php

use React\EventLoop\Factory;
use WyriHaximus\React\Doctrine\DataBaseAbstractionLayer\Pool;

require 'vendor/autoload.php';

$loop = Factory::create();
$credetials = require __DIR__ . '/credentials.php';
Pool::create($loop, $credetials)->then(function (Pool $pool) use ($argv) {
    return $pool->fetchColumn($argv[1]);
})->done(function ($results) {
    var_export($results);
    die();
}, function ($e) {
    var_export($e);
});

$loop->run();
