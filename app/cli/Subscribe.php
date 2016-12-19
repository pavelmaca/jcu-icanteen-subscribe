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

class Subscribe extends Command
{
    use SmartObject;

    protected function configure()
    {
        $this->setName('subscribe')
            ->setDescription('Add email to subscription');
        $this->addArgument('email', InputArgument::REQUIRED, 'Subscribe email:');
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

            if ($repo->subscribe($email)) {
                /** @var Notificator $ntificator */
                $ntificator = $this->getHelper('container')->getByType(Notificator::class);
                $ntificator->sendWelcome($email);
                $output->writeln('Your email \''.$email.'\' was add to subscription list.');
            }else{
                $output->writeln('Can\'t subscribe \''.$email.'\'');
                return 1;
            }

            return 0; // zero return code means everything is ok

        } catch (\Throwable $e) {
            Debugger::exceptionHandler($e, false);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }

}