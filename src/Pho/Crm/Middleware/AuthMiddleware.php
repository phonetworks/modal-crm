<?php

namespace Pho\Crm\Middleware;

use Pho\Crm\Auth;
use Pho\Crm\Traits\AuthTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class AuthMiddleware implements MiddlewareInterface
{
    use AuthTrait;

    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        $this->auth->setUser($this->getCurrentUser());

        return $requestHandler->handle($request);
    }
}
