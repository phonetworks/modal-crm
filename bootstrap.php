<?php

use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
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
 * Load Eloquent ORM
 */
$capsule = new CapsuleManager();

$capsule->addConnection([
    'driver' => config('database.driver'),
    'host' => config('database.host'),
    'database' => config('database.database'),
    'username' => config('database.username'),
    'password' => config('database.password'),
    'charset' => config('database.charset'),
    'collation' => config('database.collation'),
    'prefix' => config('database.prefix'),
]);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();


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
