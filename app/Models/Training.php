<?php

namespace Models;


use Longman\TelegramBot\DB;
use PDO;

class Training extends AbstractModel
{
    const TABLE = 'training';
    const STATUS_ACTIVE = 0;
    const STATUS_FINISHED = 1;
    protected $id;
    protected $user_id;
    protected $type;
    protected $status;

    /**
     * @param $userId
     * @return Training|null
     */
    public static function getActiveByUserId($userId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT * FROM {$table} WHERE user_id = :userId AND status = :status LIMIT 1");
        $query->execute(['userId'=>$userId, 'status'=> static::STATUS_ACTIVE]);
        $raw = $query->fetch(PDO::FETCH_ASSOC);

        if(empty($raw)) {
           return null;
        }
        $training = new self;
        $training->assign($raw);
        return $training;
    }
}