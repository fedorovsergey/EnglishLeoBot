<?php

namespace Models;


use Longman\TelegramBot\DB;
use Longman\TelegramBot\TelegramLog;
use PDO;

class User extends AbstractModel
{
    const TABLE = 'user';

    protected $id;
    protected $login;
    protected $active_training = false;

    protected static $_fields = [
        'id',
        'login',
        'password',
    ];

    public static function getById($id)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT * FROM {$table} WHERE id = :id");
        $query->execute(['id'=>$id]);
        $raw = $query->fetch(PDO::FETCH_ASSOC);
        if (empty($row)) {
            return null;
        }
        $user = new self;
        $user->assign($raw);
        return $user;
    }

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
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if (empty($row)) {
            return null;
        }
        $user = new self;
        $user->assign($row);
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

    /**
     * @return Question
     * @throws Exception
     */
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
        if(false === $this->active_training) {
            $this->active_training = Training::getActiveByUserId($this);
        }
        return $this->active_training;
    }

    /**
     * Проверка ответа пользователя
     * @param $text
     * @return bool
     */
    public function checkAnswer($text)
    {
        //предполагаем что отвечали на этот вопрос
        $question = $this->getNextQuestion();
        return $question->checkAndMarkAnswered($text);
    }

    /**
     * Возвращает окончена ли текущая тренировка и отправляет результаты, если окончена
     * @return bool
     */
    public function trainingIsFinished()
    {
        $training = $this->getActiveTraining();
        if(null === $training->getNextQuestion()) {
            $training->sendResultLingualeo();
            $training->markFinished();
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getTrainingSummaryText()
    {
        return $this->getActiveTraining()->getSummaryText();
    }
}
