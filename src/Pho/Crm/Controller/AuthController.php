<?php

namespace Pho\Crm\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class AuthController
{
    public function login(ServerRequestInterface $request)
    {
        return new HtmlResponse(view('login.php'));
    }
}
