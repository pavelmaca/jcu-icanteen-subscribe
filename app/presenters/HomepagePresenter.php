<?php

namespace App\Presenters;

use App\Model;
use App\Services\SubscribeRepository;
use Nette;


class HomepagePresenter extends BasePresenter
{
    /**
     * @inject
     * @var SubscribeRepository
     */
    public $subscribeRepository;

    public function actionUnsubscribe($email)
    {
        $this->subscribeRepository->unsubscribe($email);
        $this->flashMessage('Odběr pro ' . $email . ' byl zrušen.');
        $this->redirect('default');
    }

    public function createComponentSubscribeForm()
    {
        $form = new Nette\Application\UI\Form();
        $form->addEmail('email', 'Email')
            ->setRequired(true);

        $form->addSubmit('subscribe');

        $form->onSuccess[] = [$this, "handleSubscription"];
        return $form;
    }

    public function handleSubscription(Nette\Application\UI\Form $form)
    {
        $values = $form->getValues();
        $this->subscribeRepository->subscribe($values['email']);
        $this->flashMessage('Email ' . $values['email'] . ' byl přihlášen k odběru.');
    }

}
