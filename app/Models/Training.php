<?php

namespace Models;


use Lingualeo\Handler;
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
     * @param User $user
     * @return Training|null
     * @internal param $userId
     */
    public static function getActiveByUserId(User $user)
    {
        $training = static::getActiveFromDb($user);
        if (null === $training) {
            //запросим новую у сайта
            $training = static::getNewFromLingualeo($user);
        }
        return $training;
    }

    /**
     * Возвращает незавершенную тренировку из базы
     * @param User $user
     * @return Training|null
     */
    private static function getActiveFromDb(User $user)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT * FROM {$table} WHERE user_id = :userId AND status = :status LIMIT 1");
        $query->execute(['userId' => $user->getId(), 'status' => static::STATUS_ACTIVE]);
        $raw = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($raw)) {
            return null;
        }
        $training = new self;
        $training->assign($raw);
        return $training;
    }

    /**
     * Запрашивает новую тренировку из lingualeo
     * @param User $user
     * @return array
     */
    private static function getNewFromLingualeo(User $user)
    {
        $rawData = static::getRawTrainingDataFromLingualeo($user);

        return $rawData;
    }

    private static function getRawTrainingDataFromLingualeo(User $user)
    {
        return (new Handler())->getNewTraining($user);
    }
}