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

class Unsubscribe extends Command
{
    use SmartObject;

    protected function configure()
    {
        $this->setName('unsubscribe')
            ->setDescription('Remove email from subscription');
        $this->addArgument('email', InputArgument::REQUIRED, 'Remmove email from subscribstion:');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var SubscribeRepository $repo */
            $repo = $this->getHelper('container')->getByType(SubscribeRepository::class);

            $email = $input->getArgument('email');
            if(!Validators::isEmail($email)){
                $output->writeln('Invalid email address.');
                return 1;
            }

            $repo->unsubscribe($email);
            $output->writeln('Email \''.$email.'\' was removed from subscription list.');

            return 0; // zero return code means everything is ok

        } catch (\Throwable $e) {
            Debugger::exceptionHandler($e, false);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }

}