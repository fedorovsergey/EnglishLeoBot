<?php

namespace Models;


use Lingualeo\Exception;
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

    protected static $_fields = [
        'id',
        'user_id',
        'type',
        'status',
    ];

    public function __construct()
    {
        $this->type = 0;
        $this->status = static::STATUS_ACTIVE;
    }

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
     * ¬озвращает незавершенную тренировку из базы
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
     * «апрашивает новую тренировку из lingualeo
     * @param User $user
     * @return Training
     */
    private static function getNewFromLingualeo(User $user)
    {
        $rawData = static::getRawTrainingDataFromLingualeo($user);

        $trainingObject = new static;
        $trainingObject->user_id = $user->getId();
        $trainingObject->save();
        $trainingObject->storeQuestionsToDb($rawData);

        //заглушка чтобы работала отправка слова
        $rawData = $rawData['game'];
        foreach($rawData as $question) {
            $questionWord = $question['text'];
            return ['error_msg'=> null, 'text'=> "$questionWord"];
        }
        //end
        return $trainingObject;
    }

    private static function getRawTrainingDataFromLingualeo(User $user)
    {
        return (new Handler())->getNewTraining($user);
    }

    private function storeQuestionsToDb($rawData)
    {
        if(empty($rawData['game'])) {
            throw new Exception('Empty game data');
        }
        foreach($rawData['game'] as $questionId => $questionData) {
            $question = new Question();
            $question->setTrainingId($this->id);
            $question->storeToDb($questionData);
        }
    }
}
