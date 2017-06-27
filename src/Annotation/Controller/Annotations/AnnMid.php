<?php

namespace PhpBoot\Annotation\Annotations;

use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxExceptions;


/**
 * 声明路由使用的中间件
 */
class AnnMid extends BaseAnnotationHandler
{
    public function __construct(ControllerBuilder $container){
        $this->container = $container;
    }
    protected function handleMethod($target, $name, $value)
    {
        $params = $this->getParams($value, 2);
        count($params)>=1 or Verify::fail(new AnnotationSyntaxExceptions("\"@o-mid <middlewares>\" miss params for $target"));
        if($route = $this->container->getRoute($target)){
            $route->setMiddlewares(TypeHint::normalize($params[0], $this->container->getClassName()));
            return true;
        }
        return false;
    }

    /**
     * @var ControllerBuilder
     */
    private $container;
}