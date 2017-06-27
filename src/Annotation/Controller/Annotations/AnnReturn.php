<?php

namespace PhpBoot\Annotation\ControllerBuilder;


use PhpBoot\Annotation\BaseAnnotationHandler;
use PhpBoot\Container\ControllerBuilder;

class AnnReturn extends BaseAnnotationHandler
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
        $type = $doc = null;
        if(count($params)>0){
            try{
                $type = TypeHint::normalize($params[0], $this->container->getClassName());
                if($type == 'mixed'
                    || strpos($type, '|') !== false
                    || $type == 'null' ){
                    $type = null; // TODO 多类型如何支持?
                }
            }catch (\Exception $e){
                \Log::warning("{$this->container->getClassName()}::$target @$name $value. decide return type failed with $e->getMessage()");
            }

        }
        if(count($params)>1){
            $doc = $params[1];
        }

        $meta = $route->getActionInvoker()
            ->getReturnHandler()
            ->getMapping('$.response.content');
        if($meta){
            $meta->doc = $doc;
            $meta->type = $type;
        }

        return true;
    }

    /**
     * @var ControllerBuilder
     */
    private $container;
}