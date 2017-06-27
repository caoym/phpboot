<?php

namespace PhpBoot\Annotation\Annotations;

use FastRoute\RouteParser\Std;
use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxExceptions;
use PhpBoot\Utils\AnnotationParams;

/**
 * Class Route
 * @package Once\Annotations
 */
class AnnRoute extends BaseAnnotationHandler
{

    public function __construct(ControllerBuilder $container){
        $this->container = $container;
    }
    protected function handleMethod($target, $name, $value)
    {
        $params = new AnnotationParams($value, 3);
        $params->count()>=2 or fail(new AnnotationSyntaxExceptions("\"$name $value\" miss params for $target"));
        //TODO 错误判断: METHOD不支持, path不规范等
        $httpMethod = strtoupper($params->getParam(0));
        in_array($httpMethod, [
            'GET',
            'POST',
            'PUT',
            'HEAD',
            'PATCH',
            'OPTIONS',
            'DELETE'
        ]) or fail(new AnnotationSyntaxExceptions("unknown method http $httpMethod in $name $value"));
        //获取方法参数信息
        $rfl =  new \ReflectionClass($this->container->getClassName());
        $method = $rfl->getMethod($target);
        $methodParams = $method->getParameters();

        $docFactory  = AnnotationsVisitor::createDocBlockFactory();
        $docblock = $docFactory->create($method->getDocComment()?:"");
        //从路由中获取变量, 用于判断参数是来自路由还是请求
        $routeParser = new Std();
        $info = $routeParser->parse($params[1]); //0.4和1.0返回值不同, 不兼容
        $routeParams = [];
        foreach ($info as $i){
            if(is_array($i)){
                $routeParams[$i[0]] = true;
            }
        }
        //设置参数列表
        $paramsMeta = [];
        foreach ($methodParams as $param){
            $paramName = $param->getName();

            $source = "$.request.input.$paramName";//默认情况参数来自input, input为post+get的数据
            if(array_has($routeParams, $paramName)){ //参数来自路由
                $source = "$.request.route.$paramName";
            }elseif($httpMethod == 'GET'){
                $source = "$.request.query.$paramName"; //GET请求显示指定来自query string
            }
            $paramClass = $param->getClass();
            $paramsMeta[] = new ParamMeta($paramName,
                $source,
                $paramClass?$paramClass->getName():null,
                $param->isOptional(),
                $param->isOptional()?$param->getDefaultValue():null,
                $param->isPassedByReference(),
                null,
                ""
            );
        }

        $paramsBuilder = new ParamsBuilder($paramsMeta);

        $route = new Route(
            $httpMethod,
            $params[1],
            null,
            new ActionInvoker($target, $paramsBuilder),
            $docblock->getSummary()."\n".$docblock->getDescription()
        );

        $this->container->addRoute($target, $route);
        return true;
    }

    /**
     * 获取路径中包含的参数
     * @param $path
     */
    static function getPathParams($path){

    }
    /**
     * @var ControllerBuilder
     */
    private $container;
}