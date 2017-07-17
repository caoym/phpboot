<?php

namespace PhpBoot\Annotation\Controller\Annotations;

use FastRoute\RouteParser\Std;
use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\Controller\ControllerAnnotationHandler;
use PhpBoot\Annotation\Entity\EntityMetaLoader;
use PhpBoot\Controller\RequestHandler;
use PhpBoot\Controller\ResponseHandler;
use PhpBoot\Controller\Route;
use PhpBoot\Entity\ArrayBuilder;
use PhpBoot\Entity\MixedTypeBuilder;
use PhpBoot\Entity\ScalarTypeBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Metas\ParamMeta;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class RouteAnnotationHandler extends ControllerAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 3);
        $params->count()>=2 or fail(new AnnotationSyntaxException("The annotation @{$ann->name} of {$this->builder->getClassName()}::{$ann->parent->name} require 2 params, {$params->count()} given"));

        //TODO 错误判断: METHOD不支持, path不规范等
        $httpMethod = strtoupper($params->getParam(0));
        $target = $ann->parent->name;
        in_array($httpMethod, [
            'GET',
            'POST',
            'PUT',
            'HEAD',
            'PATCH',
            'OPTIONS',
            'DELETE'
        ]) or fail(new AnnotationSyntaxException("unknown method http $httpMethod in {$this->builder->getClassName()}::$target"));
        //获取方法参数信息
        $rfl =  new \ReflectionClass($this->builder->getClassName());
        $method = $rfl->getMethod($target);
        $methodParams = $method->getParameters();

        //从路由中获取变量, 用于判断参数是来自路由还是请求
        $routeParser = new Std();
        $info = $routeParser->parse($params->getParam(1)); //0.4和1.0返回值不同, 不兼容
        $routeParams = [];
        foreach ($info[0] as $i){
            if(is_array($i)){
                $routeParams[$i[0]] = true;
            }
        }
        //设置参数列表
        $paramsMeta = [];
        foreach ($methodParams as $param){
            $paramName = $param->getName();
            $source = "request.$paramName";
            if(array_key_exists($paramName, $routeParams)){ //参数来自路由
                $source = "request.$paramName";
            }elseif($httpMethod == 'GET'){
                $source = "request.$paramName"; //GET请求显示指定来自query string
            }
            $paramClass = $param->getClass();
            if($paramClass){
                $paramClass = $paramClass->getName();
            }
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
            if(TypeHint::isArray($paramClass)){
                $builder = ArrayBuilder::create($paramClass, $getBuilder);
            }else{
                $builder = $getBuilder($paramClass);
            }
            $paramsMeta[] = new ParamMeta($paramName,
                $source,
                $paramClass?:'mixed',
                $param->isOptional(),
                $param->isOptional()?$param->getDefaultValue():null,
                $param->isPassedByReference(),
                null,
                '',
                $builder
            );
        }

        $requestHandler = new RequestHandler($paramsMeta);
        $responseHandler = new ResponseHandler();
        $responseHandler->setMapping('response.content', new ReturnMeta('return','mixed',''));

        $route = new Route(
            $httpMethod,
            $params->getParam(1),
            $requestHandler,
            $responseHandler,
            $ann->parent->summary,
            $ann->parent->description
        );
        $this->builder->addRoute($target, $route);
    }
}