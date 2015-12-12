<?php
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
        return ['msg'=>'Hello World!'];
    }
    /**
     * @property
     */
    private $db;
}
