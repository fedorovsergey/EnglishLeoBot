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
    protected $lingualeo_id;

    protected static $_fields = [
        'id',
        'training_id',
        'text',
        'status',
        'lingualeo_id',
    ];

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
