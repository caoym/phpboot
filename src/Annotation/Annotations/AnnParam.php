<?php

namespace PhpBoot\Annotation\Annotations;
use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;


/**
 * 处理类方法的@param注释
 */
class AnnParam extends BaseAnnotationHandler
{

    public function __construct(ControllerBuilder $container){
        $this->container = $container;
    }

    protected function handleMethod($target, $name, $value)
    {
        $route = $this->container->getRoute($target);
        if($route){
            $params = $this->getParams($value, 3);
            $className = $this->container->getClassName();
            count($params) >=1 or Verify::fail(new AnnotationSyntaxExceptions("\"@param [type] <param>\" miss params for $className::$target"));
            $paramType = null;
            $paramName = null;
            $paramDoc = '';
            if(substr($params[0], 0, 1) == '$'){ //带$前缀的是变量
                $paramName = substr($params[0], 1);
                if(count($params)>1){
                    $paramDoc = $params[1];
                }
            }elseif (count($params) >=2 && substr($params[1], 0, 1) == '$'){
                $paramType = $params[0]; //TODO 检测类型是否合法
                $paramName = substr($params[1], 1);
                if(count($params)>2){
                    $paramDoc = $params[2];
                }
            }else{
                Verify::fail(new AnnotationSyntaxExceptions("\"@param [type] <param>\" syntax error for $className::$target"));
            }
            //TODO 类型先从方法声明获取, 若没有, 再通过注释获取
            $this->paramName = $paramName;
            $paramsBuilder = $route->getActionInvoker()->getParamsBuilder();
            $param = $paramsBuilder->getParam($paramName) or Verify::fail(new AnnotationSyntaxExceptions("$className::$target param $paramName not exist "));
            //TODO 检测声明的类型和注释的类型是否匹配
            if($paramType){
                $param->type = TypeHint::normalize($paramType, $className)
                or Verify::fail(new AnnotationSyntaxExceptions("$className::$target param $paramName unknown type:$paramType "));
            }
            $param->doc = $paramDoc;
            return true;

        }
        return false;
    }

    /**
     * @var ControllerBuilder
     */
    private $container;

    public $paramName='';
}