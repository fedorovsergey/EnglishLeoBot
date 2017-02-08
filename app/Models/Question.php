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

    protected static $_fields = [
        'id',
        'training_id',
        'text',
        'status',
        'lingualeo_id',
        'correct_answer_id',
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
            'text'=>$questionData['text'],
            'lingualeo_id'=>$questionData['id'],
        ])->save();

        if(empty($questionData['answers'])) {
            throw new \Exception('Empty answers data');
        }
        foreach($questionData['answers'] as $answerId => $answerData) {
            $answer = new Answer();
            $answer->assign([
                'text'=>$answerData['answerText'],
                'question_id' => $this->id,
            ])->save();
        }
    }

    public function setTrainingId($id)
    {
        $this->training_id = $id;
    }
}
