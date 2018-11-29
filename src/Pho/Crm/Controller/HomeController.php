<?php

namespace Pho\Crm\Controller;

use Zend\Diactoros\Response;

class HomeController
{
    public function index()
    {
        return new Response\HtmlResponse(view('home.php'));
    }
}
