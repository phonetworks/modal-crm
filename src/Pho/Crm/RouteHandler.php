<?php

namespace Pho\Crm;

use DI\Container;
use Pho\Crm\Exception\AppException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteHandler implements RequestHandlerInterface
{
    private $container;
    private $handler;
    private $vars;

    public function __construct(Container $container, $handler, $vars)
    {
        $this->container = $container;
        $this->handler = $handler;
        $this->vars = $vars;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $container = $this->container;
        $handler = $this->handler;
        $vars = $this->vars;

        if ($handler instanceof \Closure) {
            $response = $container->call($handler, $vars);
        }
        elseif (is_string($handler)) {
            list($className, $method) = explode('@', $handler);

            $fullClassName = "Pho\\Crm\\Controller\\{$className}";
            if (! class_exists($fullClassName)) {
                throw new AppException("class {$fullClassName} does not exist");
            }
            if ($method !== null && ! method_exists($fullClassName, $method)) {
                throw new AppException("method {$method} does not exist in class {$fullClassName}");
            }

            $controller = $container->get($fullClassName);

            if (in_array($method, [ null, '' ])) {
                if (! is_callable($controller)) {
                    throw new AppException("{$fullClassName} is not a callable");
                }
                $response = $container->call($controller, $vars);
            }
            else {
                $response = $container->call([ $controller, $method ], $vars);
            }
        }
        else {
            throw new AppException("Unsupported handler type " . gettype($handler));
        }

        return $response;
    }
}
