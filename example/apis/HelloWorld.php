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
        return "Hello World!";
    }
    
    /**
     * @route({"GET","/json"})
     * 
     */
    public function doSomething2() {
        return "Hello World!";
    }
}
