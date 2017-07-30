<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2017/2/28
 * Time: 下午4:25
 */

namespace PhpBoot\Workflow\Process;


class ProcessToken
{
    /**
     * ProcessToken constructor.
     * @param ProcessToken|null $parent
     */
    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }


    public function getName(){
        return $this->name;
    }

    public function disable(){

    }

    public function enable(){

    }
    /**
     * @return self[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(self $child){
        $this->children[] = $child;
    }
    /**
     * @return self
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * @var self
     */
    private $parent;

    /**
     * @var self[]
     */
    private $children=[];

    /**
     * @var string
     */
    private $name;
}