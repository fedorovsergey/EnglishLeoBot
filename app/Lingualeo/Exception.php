<?php

namespace Lingualeo;

class Exception extends \Exception {
    
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct('Lingualeo: '.$message, $code, $previous);
    }
}
