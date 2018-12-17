<?php

namespace Pho\Crm\Command;

use Carbon\Carbon;
use GO\Scheduler;
use Pho\Crm\Model\ServiceTicket;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleRunCommand extends Command
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setName('schedule:run');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info("{$this->getName()}: Starting at: " . Carbon::now()->toDateTimeString());
        $scheduler = new Scheduler();
        $scheduler->call(function () {
            $this->closeTicket();
        })->daily();
        $scheduler->run();
        $this->logger->info("{$this->getName()}: Stopping at: " . Carbon::now()->toDateTimeString());
    }

    public function closeTicket()
    {
        $this->logger->info("Closing Tickets");
        $interval = config('crm.close_ticket_interval');
        ServiceTicket::whereDoesntHave('serviceConversations', function ($query) use ($interval) {
            $query->whereRaw("created_at > (NOW() - INTERVAL {$interval})");
        })->update([ 'status' => ServiceTicket::STATUS_CLOSED ]);
    }
}
