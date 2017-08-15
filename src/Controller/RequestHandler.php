<?php

namespace PhpBoot\Controller;

use PhpBoot\Application;
use PhpBoot\Metas\ParamMeta;
use PhpBoot\Utils\ArrayAdaptor;
use PhpBoot\Validator\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestHandler
{
    /**
     * @param ParamMeta[] $paramMates
     */
    public function __construct(array $paramMates=[]){
        $this->paramMetas = $paramMates;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param array $params
     * @param array $reference
     * @return void
     */
    public function handle(Application $app, Request $request, array &$params, array &$reference){

        $vld = new Validator();
        $req = ['request'=>$request];
        $requestArray = new ArrayAdaptor($req);
        $inputs = [];
        foreach ($this->paramMetas as $k=>$meta){
            if($meta->isPassedByReference){
                // param PassedByReference is used to output
                continue;
            }
            $source = \JmesPath\search($meta->source, $requestArray);
            if ($source !== null){
                $source = ArrayAdaptor::strip($source);
                if($source instanceof ParameterBag){
                    $source = $source->all();
                }
                if($meta->container){
                    $inputs[$meta->name] = $meta->container->make($source);
                }else{
                    $inputs[$meta->name] = $source;
                }
                if($meta->validation){
                    $vld->rule($meta->validation, $meta->name);
                }
            }else{
                $meta->isOptional or \PhpBoot\abort(new BadRequestHttpException("the parameter \"{$meta->source}\" is missing"));
                $inputs[$meta->name] = $meta->default;
            }
        }
        $vld = $vld->withData($inputs);
        $vld->validate() or \PhpBoot\abort(
            new \InvalidArgumentException(
                json_encode(
                    $vld->errors(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            )
        );

        $pos = 0;
        foreach ($this->paramMetas as $meta){
            if($meta->isPassedByReference){
                $params[$pos] = &$reference[$meta->name];
            }else{
                $params[$pos] = $inputs[$meta->name];
            }
            $pos++;

        }
    }

    public function getParamNames(){
        return array_map(function($meta){return $meta->name;}, $this->paramMetas);
    }

    /**
     * 获取参数列表
     * @return ParamMeta[]
     */
    public function getParamMetas(){
        return $this->paramMetas;
    }

    /**
     * 获取指定参数信息
     * @param $name
     * @return ParamMeta|null
     */
    public function getParamMeta($name){
        foreach ($this->paramMetas as $meta){
            if($meta->name == $name){
                return $meta;
            }
        }
        return null;
    }

    /**
     * @param \PhpBoot\Metas\ParamMeta[] $paramMetas
     */
    public function setParamMetas($paramMetas)
    {
        $this->paramMetas = $paramMetas;
    }
    /**
     * @var ParamMeta[]
     */
    private $paramMetas = [];
}