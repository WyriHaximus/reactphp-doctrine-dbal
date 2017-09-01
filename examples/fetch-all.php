<?php

use React\EventLoop\Factory;
use WyriHaximus\React\Doctrine\DataBaseAbstractionLayer\Pool;

require 'vendor/autoload.php';

$loop = Factory::create();
$credetials = [
    'url' => 'mysql://username:password@localhost/databasename',
];
Pool::create($loop, $credetials)->then(function (Pool $pool) {
    return $pool->fetchAll('SELECT COUNT(*) AS count FROM accountse');
})->done(function ($results) {
    var_export($results);
    die();
}, function ($e) {
    var_export($e);
});

$loop->run();
