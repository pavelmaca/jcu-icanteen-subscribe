<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pavel
 * Date: 17.12.2016
 * Time: 22:01
 */

namespace App\Services;


use Nette\Caching\Cache;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class Notificator
{
    protected $cache;
    protected $repo;
    protected $mailer;

    public function __construct(Cache $cache, SubscribeRepository $repo, $host, $username, $password, $secure)
    {
        $this->cache = $cache;
        $this->repo = $repo;

        $this->mailer = new SmtpMailer([
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'secure' => $secure,
        ]);
    }


    /**
     * Read current menu and send notification to all emails
     */
    public function send()
    {
        // last date on menu from cache
        $lastCheck = $this->cache->load('lastCheck');

        // read current menu
        $reader = new MenuReader();
        $data = $reader->readCurrent();

        $msg = "Nové speciality, které je možné objednat:\n";
        $emtpy = true;

        // add new meals to fromated message
        foreach ($data as $item) {
            if ($item[0]->format('U') > $lastCheck) {
                $emtpy = false;
                $msg .= "\n" . $item[0]->format("d.m.Y") . " " . $item[2];
            }
        }


        if (!$emtpy) {
            $this->cache->save('lastCheck', $data[count($data) - 1][0]->format('U'));
        } else {
            // nothing new, just exit
            return;
        }

        // testing mailer without smtp
        //$this->mailer = new SendmailMailer();

        // send message for each user
        foreach ($this->repo->findAll() as $row) {
            $mail = new Message();
            $mail->setFrom('iCanteen notifier <franta@example.com>')
                ->addTo($row->email)
                ->setSubject('Menza - dostupné speciality')
                ->setBody($msg . "\n\n Pro odhlášení z odběru klikněte zde: https://menza-jcu.assassik.cz/unsubscribe?email=" . $row->email);

            $this->mailer->send($mail);
        }

    }

}