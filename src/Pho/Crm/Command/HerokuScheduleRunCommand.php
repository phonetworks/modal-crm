<?php

namespace Pho\Crm\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HerokuScheduleRunCommand extends Command
{
    protected function configure()
    {
        $this->setName('heroku:schedule:run');
        $this->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Seconds used to sleep', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appRoot = APP_ROOT;
        $cmd = "php $appRoot/console schedule:run";
        $outputPath = '/dev/null';
        $cmd = "$cmd > $outputPath &";
        $sleep = $input->getOption('sleep');

        while (true) {
            exec($cmd);
            sleep($sleep);
        }
    }
}
