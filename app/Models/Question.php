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
}
