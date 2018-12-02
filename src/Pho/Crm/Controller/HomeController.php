<?php

namespace Pho\Crm\Controller;

use Pho\Crm\Traits\AuthTrait;
use Zend\Diactoros\Response;

class HomeController
{
    use AuthTrait;

    public function index()
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new Response\RedirectResponse(url('login'));
        }
        return new Response\HtmlResponse(view('home.php'));
    }
}
