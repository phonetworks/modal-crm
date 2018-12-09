<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {

    $r->get('/', 'HomeController@index');

    $r->get('/login', 'AuthController@login');
    $r->post('/login', 'AuthController@loginPost');
    $r->post('/logout', 'AuthController@logoutPost');

    $r->get('/leads', 'UserController@leads');
    $r->get('/ajax/leads', 'UserController@leadsAjax');

    $r->get('/service-tickets', 'ServiceTicketController@ticketList');
    $r->get('/service-tickets/{uuid}', 'ServiceTicketController@conversation');

    $r->post('/mailgun-messages', 'MailgunController@index');

};
