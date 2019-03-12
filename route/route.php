<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {

    $r->get('/', [ 'auth', 'HomeController@index' ]);

    $r->get('/login', 'AuthController@login');
    $r->post('/login', 'AuthController@loginPost');
    $r->post('/logout', 'AuthController@logoutPost');

    $r->get('/customers/graphjs', [ 'auth', 'UserController@customersGraphjs' ]);
    $r->get('/customers/groups', [ 'auth', 'UserController@customersGroups' ]);
    $r->get('/ajax/customers', [ 'auth', 'UserController@customersAjax' ]);
    $r->get('/customers/{user_id:\d+}', [ 'auth', 'UserController@customerDetail' ]);

    $r->get('/service-tickets', [ 'auth', 'ServiceTicketController@ticketList' ]);
    $r->get('/service-tickets/{uuid}', [ 'auth', 'ServiceTicketController@conversation' ]);
    $r->post('/service-tickets/{uuid}/reply', [ 'auth', 'ServiceTicketController@replyPost' ]);
    $r->post('/service-tickets/{uuid}/close', [ 'auth', 'ServiceTicketController@close' ]);

    $r->get('/tools', [ 'auth', 'ToolsController@html' ]);


    $r->post('/mailgun-messages', 'MailgunController@index');

};
