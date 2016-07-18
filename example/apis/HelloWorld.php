<?php

use phprs\ezsql\Sql;
class Message{
    public  function __construct($msg){
        $this->msg = $msg;
    }
    public $msg;
}
/**
 * 
 * @author caoym
 * @path("/hw")
 */
class HelloWorld
{
    /** 
     * @route({"GET","/"})
     */
    public function doSomething1() {
        return new Message('Hello World!');
    }
    
    /**
     * @property({"default":"@db"}) 注入pdo实例
     */
    //private $db;
}
