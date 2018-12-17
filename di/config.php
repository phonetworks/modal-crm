<?php

use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

return [

    ServerRequestInterface::class => function () {
        return ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
    },
    ResponseInterface::class => \DI\create(Response::class),

    LoggerInterface::class => function () {
        $logger = new \Monolog\Logger('app');
        $stream = config('app.log_stream');
        $logger->pushHandler(new StreamHandler($stream));
        return $logger;
    },

];
