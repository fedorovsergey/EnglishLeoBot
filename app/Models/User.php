<?php

namespace Models;


use Longman\TelegramBot\DB;
use PDO;

class User extends AbstractModel
{
    const TABLE = 'user';

    protected $id;
    protected $login;

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
}