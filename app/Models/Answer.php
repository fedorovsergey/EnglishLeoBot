<?php

namespace Models;


class Answer extends AbstractModel
{
    const TABLE = 'answer';
    protected $id;
    protected $question_id;
    protected $text;
}
