<?php

namespace PhpBoot\Workflow\Exceptions;

class ExceptionData
{
    public function __construct($nodeName, \RuntimeException $exception){
    }

    /**
     * name of node throw the exception
     * @var string
     */
    public $fromNode;
    /**
     * name of the exception
     * @var string
     */
    public $exception;

    /**
     * error message of the exception
     * @var string
     */
    public $message;

    /**
     * error code of the exception
     * @var int
     */
    public $code;

    /**
     * user defined data
     * @var mixed
     */
    public $userData;
}