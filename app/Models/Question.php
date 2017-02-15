<?php

namespace Models;


use Database\Db;
use Longman\TelegramBot\Entities\Keyboard;
use PDO;

class Question extends AbstractModel
{
    const TABLE = 'question';
    const STATUS_ACTIVE = 0;
    const STATUS_FINISHED = 1;
    protected $id;
    protected $training_id;
    protected $text;
    protected $status;
    protected $lingualeo_id;
    protected $correct_answer_id;
    protected $answered_correct;
    protected $num;

    protected static $_fields = [
        'id',
        'training_id',
        'text',
        'status',
        'lingualeo_id',
        'correct_answer_id',
        'answered_correct',
        'num',
    ];

    public static function getActiveByTraining($trainingId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare(
            "SELECT * FROM {$table} 
             WHERE training_id = :trainingId 
                AND status = :status
             ORDER BY NUM
             LIMIT 1");
        $query->execute(['trainingId' => $trainingId, 'status' => static::STATUS_ACTIVE]);
        $raw = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($raw)) {
            return null;
        }
        $question = new self;
        $question->assign($raw);
        return $question;
    }

    /**
     * Возвращает массив id => correct для всех вопросов тренировки
     * @param $trainingId
     * @return array
     */
    public static function getAnsweredRawData($trainingId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT lingualeo_id, answered_correct FROM {$table} WHERE training_id = :trainingId");
        $query->execute(['trainingId' => $trainingId]);

        $answersArray = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $answersArray[$row['lingualeo_id']] = $row['answered_correct'];
        }
        return $answersArray;
    }

    public static function getCorrectAnswersCountByTrainingId($trainingId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare(
            "SELECT COUNT(*) FROM {$table} 
             WHERE training_id = :trainingId
             AND answered_correct = 1");
        $query->execute(['trainingId' => $trainingId]);
        return $query->fetchColumn();
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    public function ask()
    {
        return  "Вопрос {$this->getNum()} из 10\nВыберите правильный перевод слова:\n\n<b>{$this->getText()}</b>\n";
    }

    /**
     * Возвращает кнопки с ответами
     * @return Keyboard
     */
    public function getKeyboardAnswers()
    {
        $answersText = array_values($this->getAnswers());
        shuffle($answersText);
        return new Keyboard(
            [
                'keyboard' => [
                    [$answersText[0]->getText(), $answersText[1]->getText()],
                    [$answersText[2]->getText(), $answersText[3]->getText()],
                    [$answersText[4]->getText()],
                ],
                'resize_keyboard' => true,
            ]
        );
    }

    /**
     * @return Answer[]
     */
    private function getAnswers()
    {
        return Answer::getByQuestionId($this->id);
    }

    public function __construct()
    {
        $this->status = static::STATUS_ACTIVE;
    }

    public function storeToDb($questionData)
    {
        $this->assign([
            'text'=>strtolower($questionData['text']),
            'lingualeo_id'=>$questionData['id'],
        ])->save();

        if(empty($questionData['answers'])) {
            throw new \Exception('Empty answers data');
        }
        foreach($questionData['answers'] as $answerId => $answerData) {
            $answer = new Answer();
            $answer->assign([
                'text'=>strtolower($answerData['answerText']),
                'question_id' => $this->id,
            ])->save();
            if($answerId == $questionData['id']) {
                $this->assign([
                    'correct_answer_id'=>$answer->getId(),
                ])->save();
            }
        }
    }

    public function setTrainingId($id)
    {
        $this->training_id = $id;
    }

    /**
     * Проверяет ответ и помечает что уже отвечен. Возвращает текст сообщения верно/неверно
     * @param $text
     * @return string
     */
    public function checkAndMarkAnswered($text)
    {
        $usersAnswer = Answer::getByQuestionIdAndText($this->id, $text);
        $correct = (null !== $usersAnswer) && $usersAnswer->getId() == $this->getCorrectAnswerId();
        $this->markAnswered($correct);
        return $correct ? $this->getCorrectMessageText() : $this->getIncorrectMessageText($this->getCorrectAnswer());
    }

    /**
     * @return mixed
     */
    public function getCorrectAnswerId()
    {
        return $this->correct_answer_id;
    }

    private function markAnswered($correct)
    {
        $this->assign(['answered_correct' => (int) $correct, 'status' => static::STATUS_FINISHED])->save();
    }

    /**
     * @return Training
     */
    private function getTraining()
    {
        return Training::getById($this->getTrainingId());
    }

    /**
     * @return mixed
     */
    public function getTrainingId()
    {
        return $this->training_id;
    }

    /**
     * @return string
     */
    private function getCorrectMessageText()
    {
        return "<b>Верно!</b>\n";
    }

    /**
     * @param Answer $answer
     * @return string
     */
    private function getIncorrectMessageText(Answer $answer)
    {
        return "<b>Неверно!</b>\nПравильный ответ <b>{$answer->getText()}</b>\n";
    }

    /**
     * @return Answer
     */
    private function getCorrectAnswer()
    {
        $ans = Answer::getById($this->correct_answer_id);
        return $ans;
    }

    private function getNum()
    {
        return $this->num;
    }

    public function setNum($num)
    {
        $this->num = $num;
    }
}
