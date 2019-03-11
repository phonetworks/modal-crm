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

    \PDO::class => function () {
        $dbHost = config('database.host');
        $dbName = config('database.database');
        $username = config('database.username');
        $password = config('database.password');
        $conn = new \PDO("mysql:host=$dbHost;dbname=$dbName", $username, $password);
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $conn;
    },

    \Mailgun\Mailgun::class => function () {
        $key = config('mail.mailgun_key');
        $mailgun = \Mailgun\Mailgun::create($key);
        return $mailgun;
    },

    \Pho\Crm\Service\EmailService::class => function (\Mailgun\Mailgun $mailgun, LoggerInterface $logger) {
        $emailService = new \Pho\Crm\Service\EmailService($mailgun, $logger);
        $emailService->setDefaultDomain(config('mail.mailgun_domain'));
        $emailService->setDefaultFromAddress(config('mail.from.address'));
        $emailService->setDefaultFromName(config('mail.from.name'));
        return $emailService;
    },

];
