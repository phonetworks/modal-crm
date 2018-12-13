<?php

namespace Pho\Crm;

use DI\Container;
use FastRoute\Dispatcher;
use Pho\Crm\Exception\ExceptionHandler;
use Pho\Crm\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teapot\StatusCode;
use Zend\Diactoros\Response\HtmlResponse;

class RequestHandler
{
    private $container;
    private $sessionManager;

    public function __construct(Container $container, SessionManager $sessionManager)
    {
        $this->container = $container;
        $this->sessionManager = $sessionManager;
    }

    public function handle(Dispatcher $dispatcher)
    {
        $container = $this->container;

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $path = defined('PATH_INFO') ? PATH_INFO : ( $_SERVER['PATH_INFO'] ?? '/' );

        $routeInfo = $dispatcher->dispatch($httpMethod, $path);

        try {

            switch ($routeInfo[0]) {

                case Dispatcher::NOT_FOUND:
                    $response = $container->call(function (ServerRequestInterface $request, ResponseInterface $response) {
                        $response = new HtmlResponse(view('404.php'), StatusCode::NOT_FOUND);
                        return $response;
                    });
                    break;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $response = $container->call(function (ServerRequestInterface $request, ResponseInterface $response) {
                        $response = new HtmlResponse(view('405.php'), StatusCode::METHOD_NOT_ALLOWED);
                        return $response;
                    });
                    break;

                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];

                    $this->sessionManager->run();

                    $response = $this->handleFound($handler, $vars);

                    break;

                default:
                    throw new \UnexpectedValueException('Unexpected value of $routeInfo');
            }
        }
        catch (\Exception $ex) {
            $handler = $container->get(ExceptionHandler::class);
            $response = $container->call([ $handler, 'handle' ], [ $ex ]);
        }

        if (! $response instanceof ResponseInterface) {
            if (is_string($response)) {
                $response = new HtmlResponse($response);
            }
            else {
                $response = new HtmlResponse('');
            }
        }

        return $response;
    }

    public function handleFound($handler, $vars)
    {
        $container = $this->container;
        $handlerAction = $handler;
        $middlewares = [];

        if (is_array($handler)) {
            $handlerAction = current(array_values(array_slice($handler, -1)));
            $middlewareDefinitions = array_values(array_slice($handler, 0, -1));
            $middlewares = array_map(function ($middlewareDefinition) use ($container) {
                $registeredMiddlewares = config('middleware');
                if (array_key_exists($middlewareDefinition, $registeredMiddlewares)) {
                    return $container->get($registeredMiddlewares[$middlewareDefinition]);
                }
                throw new \Exception('Middleware not registered');
            }, $middlewareDefinitions);
        }
        $routeHandler = new RouteHandler($container, $handlerAction, $vars);
        $queueRequestHandler = new QueueRequestHandler($routeHandler);
        foreach ($middlewares as $middleware) {
            $queueRequestHandler->add($middleware);
        }

        $response = $container->call([ $queueRequestHandler, 'handle' ]);

        return $response;
    }
}
