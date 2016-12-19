<?php

namespace App\Cli;

use App\Services\Notificator;
use App\Services\SubscribeRepository;
use Nette\SmartObject;
use Nette\Utils\Validators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

class ListAll extends Command
{
    use SmartObject;

    protected function configure()
    {
        $this->setName('subsciption-list')
            ->setDescription('List all subscription');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var SubscribeRepository $repo */
            $repo = $this->getHelper('container')->getByType(SubscribeRepository::class);

            $output->writeln('List of subscribed emails:');
            foreach ($repo->findAll() as $row) {
                $output->writeln($row['email']);
            }

            return 0; // zero return code means everything is ok

        } catch (\Throwable $e) {
            Debugger::exceptionHandler($e, false);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }

}