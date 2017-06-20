<?php

namespace PhpBoot\Annotation\Annotations;

use PhpBoot\Annotation\ControllerAnnotationHandler;
use PhpBoot\Exceptions\AnnotationSyntaxExceptions;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\ObjectAccess;

class AnnBind extends ControllerAnnotationHandler
{
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
        if ($this->parent == null){
            Logger::warning("@$name should be used with a parent annotation");
            return false;
        }
        $params = new AnnotationParams($value, 2);
        $params->count()>0 or fail(new AnnotationSyntaxExceptions("something wrong with @$name $value"));

        ObjectAccess::isValidPath($params[0]) or fail(new AnnotationSyntaxExceptions("something wrong with @$name $value"));

        $doc = $params->getParam(1, '');

        $returnHandler = $route->getActionInvoker()->getReturnHandler();

        if ($this->parent instanceof AnnReturn){
            foreach ($returnHandler->getMappings() as $maping){
                if($maping->source == '$.return'){
                    $maping->doc = $doc;
                }
            }
            return true;
        }elseif($this->parent instanceof AnnParam){
            $paramMeta = $route->getActionInvoker()->getParamsBuilder()->getParam($this->parent->paramName);
            if($paramMeta->isPassedByReference){
                //输出绑定
                $returnHandler->setMapping($params[0], new ReturnMeta(
                    '$.params.'.$paramMeta->name, $paramMeta->type, $doc));
            }else{
                $paramMeta->source = $params[0];
            }
            return true;
        }
        \Log::warnging("@o-bind not work with parent ".get_class($this->parent));
        return false;
    }


}