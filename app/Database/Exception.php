<?php

namespace Database;


class Exception extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct('Database: '.$message, $code, $previous);
    }
}