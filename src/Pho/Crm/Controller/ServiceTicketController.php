<?php

namespace Pho\Crm\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Auth;
use Pho\Crm\Model\ServiceConversation;
use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Model\User;
use Pho\Crm\Service\EmailService;
use Psr\Http\Message\ServerRequestInterface;
use Rakit\Validation\Validator;
use Teapot\StatusCode;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class ServiceTicketController
{
    private $auth;
    private $emailService;

    public function __construct(Auth $auth, EmailService $emailService)
    {
        $this->auth = $auth;
        $this->emailService = $emailService;
    }

    public function ticketList()
    {
        $user = $this->auth->getUser();

        $tickets = ServiceTicket::query();


        error_log("Crm Role is: ".$user->crm_role);

        if($user->crm_role > 1)
            $tickets = $tickets->where('by', $user->id)->orWhere('assignee', $user->id);

        $tickets = $tickets->limit(50)
                ->offset(0)
                ->orderBy('open_date', 'desc')
                ->get();

        error_log("ticket count is: ".count($tickets));
        error_log("tickets are: ".print_r($tickets, true));

        return new HtmlResponse(view('tickets.php', [
            'tickets' => $tickets,
            'ticketStatusToText' => $this->getTicketStatusToText(),
            'ticketTypeToText' => $this->getTicketTypeToText(),
        ]));
    }

    public function conversation($uuid)
    {
        $ticket = ServiceTicket::where('uuid', $uuid)
            ->with([
                'serviceConversations' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'serviceConversations.user',
                'assigneeUser',
            ])
            ->firstOrFail();
        $by = User::where('id', $ticket->by)
            ->with([
                'instances.site',
            ])
            ->withCount([
                'accessTokens' => function ($query) {
                    $query->whereRaw('created_at > (NOW() - INTERVAL 30 DAY)');
                },
                'serviceConversations',
            ])->first();
        $conversations = $ticket->serviceConversations;

        return new HtmlResponse(view('ticket_conversation.php', [
            'ticket' => $ticket,
            'by' => $by,
            'conversations' => $conversations,
            'ticketStatusToText' => $this->getTicketStatusToText(),
            'ticketTypeToText' => $this->getTicketTypeToText(),
            'cannedResponses' => config('crm.canned_responses'),
        ]));
    }

    public function replyPost($uuid, ServerRequestInterface $request)
    {
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
                'cannedResponses' => config('crm.canned_responses'),
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
                'cannedResponses' => config('crm.canned_responses'),
                'body' => $body,
                'errors' => $errors,
            ]));
        }

        $text = $body['text'];
        $currentUser = $this->auth->getUser();
        $isRepliedByCreator = $ticket->byUser->id === $currentUser->id;
        $now = Carbon::now();

        Manager::connection()->beginTransaction();
        ServiceConversation::create([
            'uuid' => $uuid,
            'user_id' => $this->auth->getUser()->id,
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

        if (! $isRepliedByCreator) {
            $this->emailService->sendTicketReplied($ticket->uuid, $ticket->byUser->email, "{$currentUser->first_name} {$currentUser->last_name}", $currentUser->email);
        }

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

    public function close($uuid, ServerRequestInterface $request, \PDO $pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM `service-tickets` WHERE `uuid` = ?");
        $stmt->execute([ $uuid ]);
        $ticket = $stmt->fetch(\PDO::FETCH_OBJ);

        if (! $ticket) {
            return new HtmlResponse('Ticket Not Found', StatusCode::NOT_FOUND);
        }
        if ($ticket->status == ServiceTicket::STATUS_CLOSED) {
            return new HtmlResponse('Ticket already closed', StatusCode::BAD_REQUEST);
        }


        $stmt = $pdo->prepare("UPDATE `service-tickets` SET `status` = " . ServiceTicket::STATUS_CLOSED . " WHERE `uuid` = ?");
        $stmt->execute([ $uuid ]);

        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $stmt->execute([ $ticket->by ]);
        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        if (! $user) {
            return new HtmlResponse('User Not Found', StatusCode::NOT_FOUND);
        }

        $this->emailService->sendTicketClosed($ticket->uuid, $user->email);

        return new RedirectResponse(url("service-tickets/{$uuid}"));
    }

}
