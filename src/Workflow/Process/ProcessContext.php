<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2017/2/22
 * Time: 下午5:39
 */

namespace PhpBoot\Workflow\Process;


use PhpBoot\Workflow\Exceptions\ProcessRuntimeException;

class ProcessContext extends \ArrayObject
{

    /**
     * ProcessContext constructor.
     * @param ProcessContext $parent
     */
    public function __construct(self $parent = null)
    {
        parent::__construct();
        if($parent){
            foreach ($parent as $k=>$v){
                $this[$k] = $v;
            }
            $this->setToken($parent->getToken());
        }

    }

    /**
     * @return ProcessToken
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * @param ProcessToken $token
     */
    public function setToken(ProcessToken $token=null)
    {
        $this->token = $token;
    }
    /**
     * @param ProcessTaskContainer $fromNode
     * @param \Exception $e
     * @return void
     */
    public function setLastException(ProcessTaskContainer $fromNode, \Exception $e)
    {
        $this->lastException->fromNode = $fromNode->getName();
        $this->lastException->code = $e->getCode();
        $this->lastException->message = $e->getMessage();
        $this->lastException->exception = get_class($e);
        if ($e instanceof ProcessRuntimeException){
            $this->lastException->userData = $e->getUserData();
        }else{
            $this->lastException->userData = null;
        }
    }

    /**
     * @var ExceptionData
     */
    private $lastException;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $parentId;

    /**
     * @var ProcessToken
     */
    private $token;

}