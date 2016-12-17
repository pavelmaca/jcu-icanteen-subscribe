<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pavel
 * Date: 17.12.2016
 * Time: 17:00
 */

namespace App\Services;
use Nette;


class SubscribeRepository
{
    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
       // $database->query('CREATE TABLE IF NOT EXISTS subscribers(id INT(11) PRIMARY KEY, email VARCHAR(255) UNIQUE);');
    }
    /** @return Nette\Database\Table\Selection */
    public function findAll()
    {
        return $this->database->table('subscribers')->setPrimarySequence('email');
    }
    /** @return Nette\Database\Table\ActiveRow */
    public function findByMail($mail)
    {
        return $this->findAll()->get($mail);
    }
    /** @return Nette\Database\Table\ActiveRow */
    public function subscribe($email)
    {
        if($this->findAll()->where('email = ?', $email)->count() > 0){
            return;
        }
        return $this->findAll()->insert(['email' => $email]);
    }

    public function unsubscribe($email)
    {
        $this->findAll()->where('email = ?', $email)->delete();
    }
}