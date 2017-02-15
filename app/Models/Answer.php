<?php

namespace Models;


use Database\Db;
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

    /**
     * @param $questionId
     * @param $text
     * @return Answer
     */
    public static function getByQuestionIdAndText($questionId, $text)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare(
            "SELECT * FROM {$table} 
            WHERE question_id = :questionId
            AND text = :text
            LIMIT 1"
        );
        $query->execute(['questionId' => $questionId, 'text' => $text]);

        $row = $query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
        if (empty($row)) {
            return null;
        }
        $answer = new self;
        $answer->assign($row);
        return $answer;
    }

    /**
     * @param $id
     * @return Answer
     */
    public static function getById($id)
    {
        $table = static::TABLE;
        $query = Db::getPdo()->prepare(
            "SELECT * FROM {$table} 
            WHERE id = :id
            LIMIT 1"
        );
        $query->execute(['id' => $id]);

        $row = $query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
        if (empty($row)) {
            return null;
        }
        $answer = new self;
        $answer->assign($row);
        return $answer;
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
