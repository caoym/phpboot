<?php

namespace PhpBoot\Annotation\Annotations;
use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;


/**
 * Class Path
 * 用于注释controller 类对应的路由前缀, 如"@o-path /users/"
 */
class AnnPath extends BaseAnnotationHandler
{
    public function __construct(ControllerBuilder $container){
        $this->container = $container;
    }
    protected function handleClass($target, $name, $value)
    {
        $params = $this->getParams($value,2) + [''];
        $this->container->setPathPrefix($params[0]);
        return true;
    }

    /**
     * @var ControllerBuilder
     */
    private $container;

}