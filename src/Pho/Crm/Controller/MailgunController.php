<?php

namespace Pho\Crm\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Model\ServiceConversation;
use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Model\User;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\HtmlResponse;

class MailgunController
{
    public function index(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();

        $sender = $body['sender'];
        $subject = $body['subject'];
        $bodyPlain = $body['body-plain'];

        $user = User::where('email', $sender)->first();
        if ($user !== null) {
            $uuid = Uuid::uuid4();
            Manager::connection()->beginTransaction();
            ServiceTicket::create([
                'uuid' => $uuid,
                'title' => $subject,
                'type' => ServiceTicket::TYPE_SUPPORT,
                'by' => $user->id,
                'open_date' => Carbon::now(),
                'status' => ServiceTicket::STATUS_OPEN,
            ]);
            ServiceConversation::create([
                'uuid' => $uuid,
                'user_id' => $user->id,
                'text' => $bodyPlain,
                'source' => ServiceConversation::SOURCE_EMAIL,
                'created_at' => Carbon::now(),
            ]);
            Manager::connection()->commit();
        }

        return new HtmlResponse('OK');
    }
}
