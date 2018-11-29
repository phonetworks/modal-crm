<?php

use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Zend\Diactoros\Response\SapiEmitter;

include 'vendor/autoload.php';

define('APP_ROOT', __DIR__);


/**
 * Load dependency injection container
 * @var \DI\Container $container
 */
$container = require 'di/container.php';


/**
 * Load environment variables
 */
$dotenv = new Dotenv(APP_ROOT);
$dotenv->load();


/**
 * Routes
 */
$dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {

    $route = require 'route/route.php';

    $r->addGroup('', $route);
});

$requestHander = new \Pho\Crm\RequestHandler($container);
$response = $requestHander->handle($dispatcher);


/**
 * Emit response
 */
$emitter = new SapiEmitter();
$emitter->emit($response);
