<?php

namespace Models;


use Longman\TelegramBot\DB;
use PDO;

class User extends AbstractModel
{
    const TABLE = 'user';

    protected $id;
    protected $login;

    protected static $_fields = [
        'id',
        'login',
        'password',
    ];

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    protected $password;
    protected $chat_id;

    /**
     * @param $chatId
     * @return User
     */
    public static function getByChatId($chatId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT * FROM {$table} WHERE chat_id = :chatId");
        $query->execute(['chatId'=>$chatId]);
        $raw = $query->fetch(PDO::FETCH_ASSOC);

        $user = new self;
        $user->assign($raw);
        return $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCookiePath()
    {
        return ROOT  . "/cookie/{$this->getId()}.txt";
    }

    public function getNextQuestion()
    {
        $activeTraining = $this->getActiveTraining();
        $nextQuestion = $activeTraining->getNextQuestion();
        if(null == $nextQuestion) {
            throw new Exception('No more active question');
        }

        return $nextQuestion;
    }

    /**
     * @return Training
     */
    private function getActiveTraining()
    {
        return Training::getActiveByUserId($this);
    }
}