<?php

use FastRoute\RouteCollector;
use Zend\Diactoros\Response\SapiEmitter;

include __DIR__ . '/bootstrap.php';

/**
 * Routes
 */
$dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {

    $route = require 'route/route.php';

    $r->addGroup('', $route);
});

$requestHander = $container->make(\Pho\Crm\RequestHandler::class, [
    \DI\Container::class => $container,
]);
$response = $requestHander->handle($dispatcher);


/**
 * Emit response
 */
$emitter = new SapiEmitter();
$emitter->emit($response);
