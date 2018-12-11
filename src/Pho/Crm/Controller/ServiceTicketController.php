<?php

namespace Pho\Crm\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Model\ServiceConversation;
use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Traits\AuthTrait;
use Psr\Http\Message\ServerRequestInterface;
use Rakit\Validation\Validator;
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

        return new HtmlResponse(view('tickets.php', [
            'tickets' => $tickets,
            'ticketStatusToText' => $this->getTicketStatusToText(),
            'ticketTypeToText' => $this->getTicketTypeToText(),
        ]));
    }

    public function conversation($uuid)
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        $ticket = ServiceTicket::where('uuid', $uuid)
            ->with([
                'serviceConversations' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'serviceConversations.user',
                'assigneeUser',
                'byUser',
            ])
            ->firstOrFail();
        $conversations = $ticket->serviceConversations;

        return new HtmlResponse(view('ticket_conversation.php', [
            'ticket' => $ticket,
            'conversations' => $conversations,
            'ticketStatusToText' => $this->getTicketStatusToText(),
            'ticketTypeToText' => $this->getTicketTypeToText(),
            'cannedResponses' => config('content.canned_responses'),
        ]));
    }

    public function replyPost($uuid, ServerRequestInterface $request)
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        $ticket = ServiceTicket::where('uuid', $uuid)
            ->with([
                'serviceConversations' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'serviceConversations.user',
                'assigneeUser',
                'byUser',
            ])
            ->firstOrFail();
        $conversations = $ticket->serviceConversations;

        if ($ticket->status === ServiceTicket::STATUS_CLOSED) {
            return new HtmlResponse(view('ticket_conversation.php', [
                'ticket' => $ticket,
                'conversations' => $conversations,
                'ticketStatusToText' => $this->getTicketStatusToText(),
                'ticketTypeToText' => $this->getTicketTypeToText(),
                'cannedResponses' => config('content.canned_responses'),
                'fail_message' => 'Ticket already closed',
            ]));
        }

        $body = $request->getParsedBody();

        $validator = new Validator();
        $validation = $validator->validate($body, [
            'text' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            return new HtmlResponse(view('ticket_conversation.php', [
                'ticket' => $ticket,
                'conversations' => $conversations,
                'ticketStatusToText' => $this->getTicketStatusToText(),
                'ticketTypeToText' => $this->getTicketTypeToText(),
                'cannedResponses' => config('content.canned_responses'),
                'body' => $body,
                'errors' => $errors,
            ]));
        }

        $text = $body['text'];
        $currentUser = $this->getCurrentUser();
        $isRepliedByCreator = $ticket->byUser->id === $currentUser->id;
        $now = Carbon::now();

        Manager::connection()->beginTransaction();
        ServiceConversation::create([
            'uuid' => $uuid,
            'user_id' => $this->getCurrentUser()->id,
            'text' => $text,
            'source' => ServiceConversation::SOURCE_WEBSITE,
            'created_at' => $now,
        ]);
        if ($isRepliedByCreator) {
            $ticket->status = ServiceTicket::STATUS_OPEN;
            $ticket->save();
        }
        else {
            $ticket->status = ServiceTicket::STATUS_WAITING_RESPONSE;
            if ($ticket->first_response_date === null) {
                $ticket->first_response_date = $now;
            }
            $ticket->save();
        }
        Manager::connection()->commit();

        return new RedirectResponse(url("service-tickets/{$uuid}"));
    }

    public function getTicketTypeToText()
    {
        return function ($type) {
            $text = '';
            switch ($type) {
                case ServiceTicket::TYPE_SUPPORT:
                    $text = 'Support';
                    break;
            }
            return $text;
        };
    }

    public function getTicketStatusToText()
    {
        return function ($status) {
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
    }
}
