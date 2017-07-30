<?php

namespace PhpBoot\Workflow\Exceptions;

class ProcessRuntimeException extends \RuntimeException
{

    public function __construct($message = "", $code = 0, $userData = null, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->userData = $userData;
    }

    /**
     * @return mixed
     */
    public function getUserData(){
        return $this->userData;
    }
    /**
     * user defined data
     * @var mixed
     */
    private $userData;
}