<?php

namespace Models;


use Lingualeo\Exception;
use Lingualeo\Handler;
use Database\Db;
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
     * @return Training
     * @throws \Exception
     */
    private static function getNewFromLingualeo(User $user)
    {
        $rawData = static::getRawTrainingDataFromLingualeo($user);

        try {
            Db::getPdo()->beginTransaction();
            $trainingObject = new static;
            $trainingObject->user_id = $user->getId();
            $trainingObject->save();
            $trainingObject->storeQuestionsToDb($rawData);

            Db::getPdo()->commit();
        } catch (\PDOException $e) {
            Db::getPdo()->rollBack();
            throw $e;
        }
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
        $k = 1;
        foreach($rawData['game'] as $questionId => $questionData) {
            $question = new Question();
            $question->setTrainingId($this->id);
            $question->setNum($k);
            $question->storeToDb($questionData);
            $k++;
        }
    }

    public static function getById($id)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT * FROM {$table} WHERE id = :id");
        $query->execute(['id' => $id]);
        $raw = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($raw)) {
            return null;
        }
        $training = new self;
        $training->assign($raw);
        return $training;
    }

    public function getNextQuestion()
    {
        return Question::getActiveByTraining($this->id);
    }

    public function sendResultLingualeo()
    {
        return (new Handler())->sendFinishedTraining($this->getUser(), $this->getAnsweredData());
    }

    public function markFinished()
    {
        $this->assign(['status'=>static::STATUS_FINISHED])->save();
    }

    private function getUser()
    {
        return User::getById($this->getUserId());
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    private function getAnsweredData()
    {
        return Question::getAnsweredRawData($this->id);
    }

    public function getSummaryText()
    {
        $correctCount = $this->getCorrectAnswersCount();
        return "Тренировка окончена.\nВы ответили правильно на $correctCount из 10 вопросов.\nНачать новую - /startTrain";
    }

    private function getCorrectAnswersCount()
    {
        return Question::getCorrectAnswersCountByTrainingId($this->id);
    }
}
