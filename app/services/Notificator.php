<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pavel
 * Date: 17.12.2016
 * Time: 22:01
 */

namespace App\Services;


use App\Model\MealType;
use Latte\Engine;
use Nette\Caching\Cache;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\SmartObject;

class Notificator
{
    use SmartObject;

    protected $cache;
    protected $repo;
    protected $mailer;
    protected $latte;

    protected $templateDir;
    protected $webLink;

    public function __construct(Cache $cache, SubscribeRepository $repo, IMailer $mailer, $templateDir, $tempDir, $webLink)
    {
        $this->cache = $cache;
        $this->repo = $repo;
        $this->mailer = $mailer;
        $this->templateDir = $templateDir;
        $this->webLink = $webLink;

        $this->latte = new Engine();
        $this->latte->setTempDirectory($tempDir . '/mailTemp');

        $this->latte->addFilter('czechDate', function (\DateTime $date, $format) {
            static $names = ['', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'];
            return $names[$date->format('N')] . ' ' . $date->format($format);
        });
    }

    public function sendWelcome($recipient)
    {
        $emailParams = [
            'webLink' => $this->webLink,
            'unsubscribeLink' => $this->webLink . '/unsubscribe?email=' . $recipient,
        ];

        $mail = new Message();
        $mail->setFrom('iCanteen notifier <jcu@inseo.cz>')
            ->addTo($recipient)
            ->setSubject('Menza - přihlášení k odběru')
            ->setHtmlBody($this->latte->renderToString($this->templateDir . '/welcome.latte', $emailParams));
        $this->mailer->send($mail);
    }


    /**
     * Read current menu and send notification to all emails
     */
    public function send()
    {
        // read current menu
        $reader = new MenuReader();
        $meals = $reader->getCurrentMealList();

        // last date on menu from cache
        $lastCheck = $this->cache->load('lastCheck');

        $lastMealDate = null;
        $newMeals = [];
        //  dump($meals);
        foreach ($meals as $meal) {
            if ($meal->getType() == MealType::SPECIALITA && $meal->getDate()->format('U') > $lastCheck) {
                $newMeals[] = $meal;
                $lastMealDate = $meal->getDate();
            }
        }

        if (empty($newMeals)) {
            return;
        }

        $this->cache->save('lastCheck', $lastMealDate->format('U'));

        $emailParams = [
            'meals' => $newMeals,
            'icanteenLink' => 'http://menza.jcu.cz:8080',
            'webLink' => $this->webLink,
        ];
        $emailFrom = 'iCanteen notifier <jcu@inseo.cz>';
        $emailSubject = 'Menza JCU - Objednat speciality';

        // send message for each user
        foreach ($this->repo->findAll() as $row) {
            $emailParams['userEmail'] = $row->email;
            $emailParams['unsubscribeLink'] = $this->webLink . '/unsubscribe?email=' . $row->email;

            $mail = new Message();
            $mail->setFrom($emailFrom)
                ->addTo($emailParams['userEmail'])
                ->setSubject($emailSubject)
                ->setHtmlBody($this->latte->renderToString($this->templateDir . '/update.latte', $emailParams));
            $this->mailer->send($mail);
        }

    }
}