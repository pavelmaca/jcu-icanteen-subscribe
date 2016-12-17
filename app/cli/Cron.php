<?php

namespace App\Cli;

use App\Services\Notificator;
use Nette\SmartObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

class Cron extends Command
{
    use SmartObject;

    protected function configure()
    {
        $this->setName('notification:send')
            ->setDescription('Send notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var Notificator $notificator */
            $notificator = $this->getHelper('container')->getByType(Notificator::class);
            $notificator->send();

            return 0; // zero return code means everything is ok

        } catch (\Throwable $e) {
            Debugger::exceptionHandler($e, false);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }

}