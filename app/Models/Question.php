<?php

namespace Models;


use Longman\TelegramBot\DB;
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

    protected static $_fields = [
        'id',
        'training_id',
        'text',
        'status',
        'lingualeo_id',
        'correct_answer_id',
        'answered_correct',
    ];

    public static function getActiveByTraining($trainingId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare("SELECT * FROM {$table} WHERE training_id = :trainingId AND status = :status LIMIT 1");
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
        return  "Выберите правильный переод слова:\n\n{$this->getText()}\n";
    }

    /**
     * Возвращает кнопки с ответами
     * @return Keyboard
     */
    public function getKeyboardAnswers()
    {
        $answersText = array_values($this->getAnswers());
        return new Keyboard(
            [$answersText[0]->getText(), $answersText[1]->getText()],
            [$answersText[2]->getText(), $answersText[3]->getText()],
            [$answersText[4]->getText()]
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
     * Проыеряет ответ и помечает что уже отвечен
     * @param $text
     * @return bool
     */
    public function checkAndMarkAnswered($text)
    {
        $correctAnswer = Answer::getByQuestionIdAndText($this->id, $text);
        $correct = (null !== $correctAnswer) && $correctAnswer->getId() == $this->getCorrectAnswerId();
        $this->markAnswered($correct);
        return $correct;
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
        $training = $this->getTraining();
        if(null === $training->getNextQuestion()) {
            $training->sendResultLingualeo();
            $training->markFinished();
        }
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
}
