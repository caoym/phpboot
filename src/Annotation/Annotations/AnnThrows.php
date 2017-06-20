<?php

namespace PhpBoot\Annotation\Annotations;

use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxExceptions;

class AnnThrows extends BaseAnnotationHandler
{
    public function __construct(ControllerBuilder $container){
        $this->container = $container;
    }

    /**
     * @param $target
     * @param $name
     * @param $value
     * @return bool
     */
    protected function handleMethod($target, $name, $value)
    {
        $route = $this->container
            ->getRoute($target);

        if(!$route){
            return false;
        }
        $params = $this->getParams($value, 2);
        count($params)>0 or Verify::fail(new AnnotationSyntaxExceptions("something wrong with @throw $value"));

        $type = TypeHint::normalize($params[0], $this->container->getClassName()); // TODO 缺少类型时忽略错误
        $doc = count($params)>0?$params[1]:'';

        $route->getActionInvoker()
            ->addExceptions($type, $doc);
        return true;
    }

    /**
     * @var ControllerBuilder
     */
    private $container;
}