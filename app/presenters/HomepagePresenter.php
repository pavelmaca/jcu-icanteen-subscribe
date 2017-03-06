<?php

namespace App\Presenters;

use App\Services\Notificator;
use App\Services\SubscribeRepository;
use Nette;


class HomepagePresenter extends BasePresenter
{
    /**
     * @inject
     * @var SubscribeRepository
     */
    public $subscribeRepository;

    public function actionDefault()
    {
        /** @var Notificator $notificatro */
        // $notificatro = $this->context->getByType(Notificator::class);
        // $notificatro->send();
    }

    public function actionUnsubscribe($email)
    {
        /*  if(!Nette\Utils\Validators::isEmail($email)){
              $this->flashMessage('Neplatný email.', 'alert-danger');
              $this->redirect('default');
          }*/
        $this->subscribeRepository->unsubscribe($email);
        $this->flashMessage('Odběr pro ' . $email . ' byl zrušen.', 'alert-info');
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
        if ($this->subscribeRepository->subscribe($values['email'])) {
            $this->flashMessage('Email ' . $values['email'] . ' byl přihlášen k odběru.', 'alert-success');
            $this->context->getService('notificator')->sendWelcome($values['email']);
        }
    }

}
