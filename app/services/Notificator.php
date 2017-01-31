<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pavel
 * Date: 17.12.2016
 * Time: 22:01
 */

namespace App\Services;


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

    public function __construct(Cache $cache, SubscribeRepository $repo, IMailer $mailer)
    {
        $this->cache = $cache;
        $this->repo = $repo;
        $this->mailer = $mailer;
    }

    public function sendWelcome($recipient)
    {
        $mail = new Message();
        $mail->setFrom('iCanteen notifier <jcu@inseo.cz>')
            ->addTo($recipient)
            ->setSubject('Menza - přihlášení k odběru')
            ->setBody("Děkujeme za přihlášení k odběru jídelníčku mez JČU.
            \nV okamžiku zveřejnění nových specialit budete informován/a.\n
            \nPokud jste se pro odběr nepřihlásil/a záměrně, odhlásit se můžete kliknutím na následující odkaz: https://menza-jcu.assassik.cz/unsubscribe?email=" . $recipient);

        $this->mailer->send($mail);
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

        // send message for each user
        foreach ($this->repo->findAll() as $row) {
            $mail = new Message();
            $mail->setFrom('iCanteen notifier <jcu@inseo.cz>')
                ->addTo($row->email)
                ->setSubject('Menza - dostupné speciality')
                ->setBody($msg . "\n\n Pro odhlášení z odběru klikněte zde: https://menza-jcu.assassik.cz/unsubscribe?email=" . $row->email);

            $this->mailer->send($mail);
        }

    }

}