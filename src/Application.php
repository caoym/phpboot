<?php
namespace PhpBoot;

class Application
{
    public function make($className){
        return new $className;
    }
}