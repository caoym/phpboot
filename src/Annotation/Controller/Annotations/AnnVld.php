<?php
namespace PhpBoot\Annotation\Annotations;


use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxExceptions;

class AnnVld extends BaseAnnotationHandler
{
    public function __construct(ControllerBuilder $container, BaseAnnotationHandler $parent=null){
        $this->container = $container;
        $this->parent = $parent;
    }

    /**
     * @param $target
     * @param $name
     * @param $value
     * @return bool
     */
    protected function handleMethod($target, $name, $value)
    {
        $route = $this->container->getRoute($target);
        if(!$route) {
            return false;
        }
        if ($this->parent ==null){
            \Log::warnging("@$name should be used with a parent annotation");
            return false;
        }
        $params = $this->getParams($value, 2);
        count($params)>0 or Verify::fail(new AnnotationSyntaxExceptions("something wrong with @o-vld $value"));

        $doc = count($params)>0?$params[1]:'';

        if($this->parent instanceof AnnParam){
            $paramMeta = $route->getActionInvoker()->getParamsBuilder()->getParam($this->parent->paramName);
            $paramMeta->validation = $params[0];
            $paramMeta->doc = $doc;
            return true;
        }
        \Log::warnging("@o-vld not work with parent ".get_class($this->parent));
        return false;
    }

    /**
     * @var ControllerBuilder
     */
    private $container;

    /**
     * @var AnnParam
     */
    private $parent;
}