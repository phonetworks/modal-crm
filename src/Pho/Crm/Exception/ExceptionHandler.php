<?php

namespace Pho\Crm\Exception;

use Teapot\StatusCode;
use Zend\Diactoros\Response\HtmlResponse;

class ExceptionHandler
{
    public function handle(\Exception $ex)
    {
        switch (get_class($ex)) {

            default:
                $response = new HtmlResponse(view('500.php'), StatusCode::INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}
