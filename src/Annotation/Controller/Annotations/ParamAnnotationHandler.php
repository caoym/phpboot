<?php

namespace PhpBoot\Annotation\controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\Controller\ControllerAnnotationHandler;
use PhpBoot\Annotation\Entity\EntityMetaLoader;
use PhpBoot\Entity\ArrayBuilder;
use PhpBoot\Entity\MixedTypeBuilder;
use PhpBoot\Entity\ScalarTypeBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class ParamAnnotationHandler extends ControllerAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        if(!$ann->parent){
            return;
        }
        $target = $ann->parent->name;
        $route = $this->builder->getRoute($target);
        if(!$route){
            return ;
        }
        $params = new AnnotationParams($ann->description, 3);
        $className = $this->builder->getClassName();
        $params->count() >=1 or fail(new AnnotationSyntaxException("{$this->builder->getClassName()}::{$ann->parent->name} @{$ann->name} syntax error, missing params"));
        $paramType = null;
        $paramName = null;
        $paramDoc = '';
        if(substr($params->getParam(0), 0, 1) == '$'){ //带$前缀的是变量
            $paramName = substr($params->getParam(0), 1);
            if($params->count()>1){
                $paramDoc = $params[1];
            }
        }elseif ($params->count() >=2 && substr($params->getParam(1), 0, 1) == '$'){
            $paramType = $params->getParam(0); //TODO 检测类型是否合法
            $paramName = substr($params->getParam(1), 1);
            if($params->count() >2){
                $paramDoc = $params->getRawParam(2);
            }
        }else{
            fail(new AnnotationSyntaxException("{$this->builder->getClassName()}::{$ann->parent->name} @{$ann->name} syntax error"));
        }

        $paramMeta = $route->getRequestHandler()->getParamMeta($paramName);
        $paramMeta or fail(new AnnotationSyntaxException("$className::$target param $paramName not exist "));
        //TODO 检测声明的类型和注释的类型是否匹配
        if($paramType){
            $paramMeta->type = TypeHint::normalize($paramType, $className)
            or fail(new AnnotationSyntaxException("{$this->builder->getClassName()}::{$ann->parent->name} @{$ann->name} syntax error, param $paramName unknown type:$paramType "));
            $getBuilder = function($type){
                if(!$type || $type == 'mixed'){
                    return new MixedTypeBuilder();
                }elseif (TypeHint::isScalarType($type)){
                    return new ScalarTypeBuilder($type);
                }else{
                    $loader = new EntityMetaLoader();
                    return $loader->loadFromClass($type);
                }
            };
            if(TypeHint::isArray($paramMeta->type)){
                $builder = ArrayBuilder::create($paramMeta->type, $getBuilder);
            }else{
                $builder = $getBuilder($paramMeta->type);
            }
            $paramMeta->builder = $builder;
        }
        $paramMeta->description = $paramDoc;
    }
}