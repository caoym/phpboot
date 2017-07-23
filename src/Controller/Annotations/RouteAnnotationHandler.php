<?php

namespace PhpBoot\Controller\Annotations;

use FastRoute\RouteParser\Std;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\ExceptionHandler;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\RequestHandler;
use PhpBoot\Controller\ResponseHandler;
use PhpBoot\Controller\Route;
use PhpBoot\Entity\MixedTypeContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Metas\ParamMeta;
use PhpBoot\Utils\AnnotationParams;

class RouteAnnotationHandler
{
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @param EntityContainerBuilder $entityBuilder
     */
    public function __invoke(ControllerContainer $container, $ann, EntityContainerBuilder $entityBuilder)
    {
        $params = new AnnotationParams($ann->description, 3);
        $params->count()>=2 or \PhpBoot\abort(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::{$ann->parent->name} require 2 params, {$params->count()} given"));

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
        ]) or \PhpBoot\abort(new AnnotationSyntaxException("unknown method http $httpMethod in {$container->getClassName()}::$target"));
        //获取方法参数信息
        $rfl =  new \ReflectionClass($container->getClassName());
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

        $responseHandler = new ResponseHandler();
        $exceptionHandler = new ExceptionHandler();

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
            $entityContainer = ContainerFactory::create($entityBuilder, $paramClass);
            $meta = new ParamMeta($paramName,
                $source,
                $paramClass?:'mixed',
                $param->isOptional(),
                $param->isOptional()?$param->getDefaultValue():null,
                $param->isPassedByReference(),
                null,
                '',
                $entityContainer
            );
            $paramsMeta[] = $meta;
            if($meta->isPassedByReference){
                $responseHandler->setMapping('response.content.'.$meta->name, new ReturnMeta(
                    'params.'.$meta->name,
                    $meta->type, $meta->description,
                    ContainerFactory::create($entityBuilder, $meta->type)
                ));
            }
        }
        $requestHandler = new RequestHandler($paramsMeta);

        $responseHandler->setMapping('response.content', new ReturnMeta('return','mixed','', new MixedTypeContainer()));

        $uri = $params->getParam(1);
        $uri = rtrim($container->getPathPrefix(), '/').'/'.ltrim($uri, '/');
        $route = new Route(
            $httpMethod,
            $uri,
            $requestHandler,
            $responseHandler,
            $exceptionHandler,
            [],
            $ann->parent->summary,
            $ann->parent->description
        );
        $container->addRoute($target, $route);
    }
}