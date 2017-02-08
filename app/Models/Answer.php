<?php

namespace Models;


use Longman\TelegramBot\DB;
use PDO;

class Answer extends AbstractModel
{
    const TABLE = 'answer';
    protected $id;
    protected $question_id;
    protected $text;

    protected static $_fields = [
        'id',
        'question_id',
        'text',
    ];

    /**
     * @param $questionId
     * @return Answer[]
     */
    public static function getByQuestionId($questionId)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare(
            "SELECT * FROM {$table} 
            WHERE question_id = :questionId"
        );
        $query->execute(['questionId' => $questionId]);

        $answersArray = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $answer = new self;
            $answer->assign($row);
            $answersArray[$row['id']] = $answer;
        }
        if (empty($answersArray)) {
            return null;
        }
        return $answersArray;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getId()
    {
        return $this->id;
    }
}
