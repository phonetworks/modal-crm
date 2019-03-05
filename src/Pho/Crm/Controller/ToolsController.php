<?php

namespace Pho\Crm\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Auth;
use Pho\Crm\Model\ServiceConversation;
use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Model\User;
use Psr\Http\Message\ServerRequestInterface;
use Rakit\Validation\Validator;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class ToolsController
{
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function html()
    {
        $user = $this->auth->getUser();
/*
        $tickets = ServiceTicket::query();

        $tickets = $tickets->where('by', $user->id)->orWhere('assignee', $user->id)
            ->limit(20)
            ->offset(0)
            ->orderBy('open_date', 'desc')
            ->get();
            */

        return new HtmlResponse(view('tools.php', [
            //'tickets' => $tickets,
            //'ticketStatusToText' => $this->getTicketStatusToText(),
            //'ticketTypeToText' => $this->getTicketTypeToText(),
        ]));
    }

}
