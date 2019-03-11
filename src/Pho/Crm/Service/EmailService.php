<?php

namespace Pho\Crm\Service;

use Mailgun\Mailgun;
use Psr\Log\LoggerInterface;

class EmailService
{
    protected $mailgun;
    protected $logger;
    protected $configs;
    protected $defaultDomain;
    protected $defaultFromAddress;
    protected $defaultFromName;

    public function __construct(Mailgun $mailgun, LoggerInterface $logger)
    {
        $this->mailgun = $mailgun;
        $this->logger = $logger;
    }

    public function setDefaultDomain($domain)
    {
        $this->defaultDomain = $domain;
    }

    public function setDefaultFromAddress($fromAddress)
    {
        $this->defaultFromAddress = $fromAddress;
    }

    public function setDefaultFromName($fromName)
    {
        $this->defaultFromName = $fromName;
    }

    public function email($to, $subject, array $opts = [])
    {
        $domain = $opts['domain'] ?? $this->defaultDomain;
        $from = $opts['from'] ?? "{$this->defaultFromName} <{$this->defaultFromAddress}>";
        $params = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'text' => $opts['text'],
            'html' => $opts['html'],
        ];
        $this->mailgun->messages()->send($domain, $params);
    }

    public function sendTicketOpened($ticketUuid, $creatorEmail)
    {
        $ticketUrl = url(sprintf('service-tickets/%s', $ticketUuid));
        $viewModel = [
            'ticketUrl' => $ticketUrl,
        ];
        $this->email($creatorEmail, 'Ticket Opened', [
            'text' => view('email/ticket_opened.text.php', $viewModel),
            'html' => view('email/ticket_opened.html.php', $viewModel),
        ]);
    }

    public function sendTicketReplied($ticketUuid, $creatorEmail, $repliedByName, $repliedByEmail)
    {
        $ticketUrl = url(sprintf('service-tickets/%s', $ticketUuid));
        $viewModel = [
            'repliedByName' => $repliedByName,
            'repliedByEmail' => $repliedByEmail,
            'ticketUrl' => $ticketUrl,
        ];
        $this->email($creatorEmail, 'Ticket Created', [
            'text' => view('email/ticket_replied.text.php', $viewModel),
            'html' => view('email/ticket_replied.html.php', $viewModel),
        ]);
    }

    public function sendTicketClosed($ticketUuid, $creatorEmail)
    {
        $ticketUrl = url(sprintf('service-tickets/%s', $ticketUuid));
        $viewModel = [
            'ticketUrl' => $ticketUrl,
        ];
        $this->email($creatorEmail, 'Ticket Created', [
            'text' => view('email/ticket_closed.text.php', $viewModel),
            'html' => view('email/ticket_closed.html.php', $viewModel),
        ]);
    }

}
