<?php

namespace PhpBoot\Container;
use Once\Metas\ReturnMeta;


/**
 * Class ActionInvoker
 * @package once
 */
class ActionInvoker
{
    /**
     * ActionInvoker constructor.
     * @param string $actionName class method name
     * @param ParamsBuilder $paramsBuilder
     */
    public function __construct($actionName, ParamsBuilder $paramsBuilder){
        $this->actionName = $actionName;
        $this->paramsBuilder = $paramsBuilder;
        $this->returnHandler = new ReturnHandler();
        //默认控制器返回值为http 响应body
        $this->returnHandler->setMapping('$.response.content', new ReturnMeta('$.return',null,''));

    }
    /**
     * @param object $instance
     * @param Context $context
     * @return void
     */
    public function invoke($instance, Context $context){
        $params = [];
        $refbuf = [];
        $this->paramsBuilder->build($context, $params, $refbuf);
        $res = call_user_func_array([$instance, $this->actionName], $params);
        $this->returnHandler->handle($context, $res, array_combine(
                $this->paramsBuilder->getParamNames(),
                $params
                )
        );
    }
    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return ParamsBuilder
     */
    public function getParamsBuilder()
    {
        return $this->paramsBuilder;
    }

    /**
     * @return ReturnHandler
     */
    public function getReturnHandler()
    {
        return $this->returnHandler;
    }

    /*
     * @return array
     * 返回包含异常类型和描述的数组
     * 示例
     * [
     *      ['NotFoundHttpException', '这是说明'],
     *      ['ForbiddenHttpException', '这是说明'],
     * ]
     */
    public function getExceptions(){
        return $this->exceptions;
    }

    /**
     * @param string $name
     * @param string $doc
     */
    public function addExceptions($name, $doc){
        $this->exceptions[] = [$name, $doc];
    }

    /**
     * @var ParamsBuilder
     */
    private $paramsBuilder;

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var ReturnHandler
     */
    private $returnHandler;

    /**
     * @var array
     * 示例
     * [
     *      ['name'=>'NotFoundHttpException', '这是说明'],
     *      ['name'=>'ForbiddenHttpException', '这是说明'],
     * ]
     */
    private $exceptions = [];

}