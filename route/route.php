<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {

    $r->get('/', [ 'auth', 'HomeController@index' ]);

    $r->get('/login', 'AuthController@login');
    $r->post('/login', 'AuthController@loginPost');
    $r->post('/logout', 'AuthController@logoutPost');

    $r->get('/leads', [ 'auth', 'UserController@leads' ]);
    $r->get('/ajax/leads', [ 'auth', 'UserController@leadsAjax' ]);
    $r->get('/leads/{user_id:\d+}', [ 'auth', 'UserController@leadDetail' ]);

    $r->get('/service-tickets', [ 'auth', 'ServiceTicketController@ticketList' ]);
    $r->get('/service-tickets/{uuid}', [ 'auth', 'ServiceTicketController@conversation' ]);
    $r->post('/service-tickets/{uuid}/reply', [ 'auth', 'ServiceTicketController@replyPost' ]);

    $r->post('/mailgun-messages', 'MailgunController@index');

};
