<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class ParamAnnotationHandler
{

    static public function getParamInfo($text)
    {

        $paramType = null;
        $paramName = null;
        $paramDoc = '';
        if(substr($text, 0, 1) == '$'){ //带$前缀的是变量
            $params = new AnnotationParams($text, 2);
            $paramName = substr($params->getParam(0), 1);
            $paramDoc = $params->getRawParam(1, '');
        }else{
            $params = new AnnotationParams($text, 3);
            if ($params->count() >=2 && substr($params->getParam(1), 0, 1) == '$'){
                $paramType = $params->getParam(0); //TODO 检测类型是否合法
                $paramName = substr($params->getParam(1), 1);
                $paramDoc = $params->getRawParam(2, '');
            }else{
                \PhpBoot\abort(new AnnotationSyntaxException("@param $text syntax error"));
            }
        }
        return [$paramType, $paramName, $paramDoc];
    }
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @param EntityContainerBuilder $entityBuilder
     */
    public function __invoke(ControllerContainer $container, $ann, EntityContainerBuilder $entityBuilder)
    {
        if(!$ann->parent){
            //Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()} should be used with parent route");
            return;
        }
        $target = $ann->parent->name;
        $route = $container->getRoute($target);
        if(!$route){
            //Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target should be used with parent route");
            return ;
        }
        $className = $container->getClassName();

        list($paramType, $paramName, $paramDoc) = self::getParamInfo($ann->description);

        $paramMeta = $route->getRequestHandler()->getParamMeta($paramName);
        $paramMeta or \PhpBoot\abort(new AnnotationSyntaxException("$className::$target param $paramName not exist "));
        //TODO 检测声明的类型和注释的类型是否匹配
        if($paramType){
            $paramMeta->type = TypeHint::normalize($paramType, $className);//or \PhpBoot\abort(new AnnotationSyntaxException("{$container->getClassName()}::{$ann->parent->name} @{$ann->name} syntax error, param $paramName unknown type:$paramType "));
            $container = ContainerFactory::create($entityBuilder, $paramMeta->type);
            $paramMeta->container = $container;
        }
        $paramMeta->description = $paramDoc;

        $responseHandler = $route->getResponseHandler();
        if($paramMeta->isPassedByReference && $responseHandler){
            $mappings = $responseHandler->getMappings();
            foreach ($mappings as $k => $v){
                if($v->source == 'params.'.$paramMeta->name){
                    $v->description = $paramMeta->description;
                    $v->type = $paramMeta->type;
                    $v->container = $paramMeta->container;
                }
            }
        }

    }
}