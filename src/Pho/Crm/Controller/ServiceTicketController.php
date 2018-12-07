<?php

namespace Pho\Crm\Controller;

use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Traits\AuthTrait;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class ServiceTicketController
{
    use AuthTrait;

    public function ticketList()
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        $user = $this->getCurrentUser();

        $tickets = ServiceTicket::query();

        $tickets = $tickets->where('by', $user->id)->orWhere('assignee', $user->id)
            ->limit(20)
            ->offset(0)
            ->orderBy('open_date', 'desc')
            ->get();

        $ticketTypeToText = function ($type) {
            $text = '';
            switch ($type) {
                case ServiceTicket::TYPE_SUPPORT:
                    $text = 'Support';
                    break;
            }
            return $text;
        };

        $ticketStatusToText = function ($status) {
            $text = '';
            switch ($status) {
                case ServiceTicket::STATUS_OPEN:
                    $text = 'Open';
                    break;
                case ServiceTicket::STATUS_WAITING_RESPONSE:
                    $text = 'Waiting Response';
                    break;
                case ServiceTicket::STATUS_CLOSED:
                    $text = 'Closed';
                    break;
            }
            return $text;
        };

        return new HtmlResponse(view('tickets.php', [
            'tickets' => $tickets,
            'ticketStatusToText' => $ticketStatusToText,
            'ticketTypeToText' => $ticketTypeToText,
        ]));
    }
}
