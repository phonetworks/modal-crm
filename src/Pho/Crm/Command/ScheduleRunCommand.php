<?php

namespace Pho\Crm\Command;

use GO\Scheduler;
use Pho\Crm\Model\ServiceTicket;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleRunCommand extends Command
{
    protected function configure()
    {
        $this->setName('schedule:run');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scheduler = new Scheduler();
        $scheduler->call(function () {
            $this->closeTicket();
        })->daily();
        $scheduler->run();
    }

    public function closeTicket()
    {
        $interval = config('crm.close_ticket_interval');
        ServiceTicket::whereDoesntHave('serviceConversations', function ($query) use ($interval) {
            $query->whereRaw("created_at > (NOW() - INTERVAL {$interval})");
        })->update([ 'status' => ServiceTicket::STATUS_CLOSED ]);
    }
}
