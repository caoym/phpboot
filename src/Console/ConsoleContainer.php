<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/6/14
 * Time: 下午2:10
 */

namespace PhpBoot\Console;


use DI\FactoryInterface;

class ConsoleContainer
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $moduleName;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $summary;
    /**
     * @var Command[]
     */
    private $commands = [];
    /**
     * @var object
     */
    private $instance;

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    public function getCommand($target)
    {
        return isset($this->commands[$target])?$this->commands[$target]:null;
    }
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    public function setModuleName($name)
    {
        $this->moduleName = $name;
    }

    public function addCommand($target, Command $command)
    {
        $this->commands[$target] = $command;
    }
    public function postCreate()
    {
        foreach ($this->commands as $command){
            $command->postCreate($this);
        }
    }
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @return mixed|object
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function getInstance(FactoryInterface $factory)
    {
        if(!$this->instance ){
            $this->instance  = $factory->make($this->getClassName());
        }
        return $this->instance;
    }
}