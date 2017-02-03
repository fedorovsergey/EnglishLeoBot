<?php

namespace Models;

class Exception extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct('Models: '.$message, $code, $previous);
    }
}
