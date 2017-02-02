<?php

namespace Models;


class Question extends AbstractModel
{
    const TABLE = 'question';
    const STATUS_ACTIVE = 0;
    const STATUS_FINISHED = 1;
    protected $id;
    protected $training_id;
    protected $text;
    protected $status;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function __construct()
    {
        $this->status = static::STATUS_ACTIVE;
    }

    public function storeToDb($questionData)
    {
        $this->save([
            'training_id'=>$this->training_id,
            'text'=>$questionData['text'],
            'status'=>$this->status,
            'lingualeo_id'=>$questionData['id'],
        ]);

        if(empty($questionData['answers'])) {
            throw new \Exception('Empty answers data');
        }
        foreach($questionData['answers'] as $answerId => $answerData) {
            $answer = new Answer();
            $answer->save([
                'text'=>$answerData['answerText'],
                'question_id' => $this->id,
            ]);
        }
    }

    public function setTrainingId($id)
    {
        $this->training_id = $id;
    }
}
