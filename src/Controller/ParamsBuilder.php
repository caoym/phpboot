<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/11/1
 * Time: 下午2:43
 */

namespace Once\Container;

use Laravel\Lumen\Application;
use Once\Metas\ParamMeta;
use Once\Utils\TypeHint;
use Once\Utils\Validator;
use Once\Utils\Verify;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Once\Metas\EntityMeta;

/**
 * Class ParamsBuilder
 * 用于从请求的上下文中提取action的输入参数
 * @package Once\route
 */
class ParamsBuilder
{
    /**
     * ParamsBuilder constructor.
     * @param ParamMeta[] $params
     */
    public function __construct(array $params){
        $this->paramsMeta = $params;
    }

    /**
     * @param Context $context
     * @param array $params
     * @param array $refbuf
     * @return array
     */
    public function build(Context $context, array &$params, array &$refbuf){

        $vldData = [];
        $vld = new Validator($context->getApp());
        foreach ($this->paramsMeta as $meta){
            $src = $meta->source;
            if ($context->hasPath($src)){
                if($meta->isPassedByReference){

                }else{
                    $value = $context->getByPath($src); //TODO 到处都是这种代码
                    if(is_string($value) && TypeHint::isArray($meta->type)){
                        $ori = $value;
                        $value = json_decode($value, true);
                        !json_last_error() or Verify::fail(new BadRequestHttpException("param $src required {$meta->type}, data $ori given, ".json_last_error_msg()));
                    }
                    $vldData[$meta->name] = $value;
                }

                $vld->addRule($meta->name, $meta->type, $meta->validation);
            }else{
                $meta->isOptional or Verify::fail(new BadRequestHttpException("param $src is required"));
                $vldData[$meta->name] = $meta->default;
            }
        }

        try{
            $vldData = $vld->validate($vldData);
        }catch (\Exception $e){
            Verify::fail(new BadRequestHttpException($e->getMessage()), $e);
        }
        $pos = 0;
        foreach ($this->paramsMeta as $meta){
            if($meta->isPassedByReference){
                $refbuf[$pos] = null;
                $params[$pos] = &$refbuf[$pos];
            }else{
                $params[$pos] = $vldData[$meta->name];
            }
            $pos++;

        }
        return true;
    }
    public function getParamNames(){
        return array_map(function($meta){return $meta->name;}, $this->paramsMeta);
    }
    /**
     * 将变量转换为指定类型
     * 只处理array->object, 原始类型不做转换, 留给下一步校验参数时转换
     *
     * @param Application $app
     * @param mixed|null $value
     * @param ParamMeta $meta
     * @return mixed
     */
    public function cast(Application $app, $value, ParamMeta $meta){
        if($value === null){
            return $value;
        }
        //原始类型不做转换, 由下一步校验参数时转换
        if(in_array($meta->type, [
            null,
            'int',
            'integer',
            'string',
            'bool',
            'boolean',
            'float',
            'double'
        ])){
            return $value;
        }

        if($meta->type == 'array'){
            is_array($value) or Verify::fail(
                new BadRequestHttpException("param {$meta->source} expects to be array"));
            return $value;
        }else{

            return $this->arrayToObject($app, $value, $meta);
        }
    }

    /**
     * @param Application $app
     * @param $value
     * @param ParamMeta $meta
     * @return mixed
     */
    public function arrayToObject(Application $app, $value, ParamMeta $meta){
        if($value === null){
            return $value;
        }
        is_array($value) or Verify::fail(
            new BadRequestHttpException("param {$meta->source} expects to be {$meta->type}")
        );

        $entityMeta = EntityMeta::getFromClass($meta->type);
        $res = $app->make($meta->type);
        foreach ($entityMeta->getProperties() as $property){
            if(array_has($value, $property->name)){
                $res->{$property->name} = $value;
            }elseif(!$property->isOptional()){
                Verify::fail(new BadRequestHttpException("invalid param: {$meta->source}({$meta->type}), property {$property->name} is required"));
            }
        }
        return $res;

    }
    /**
     * 获取参数列表
     * @return ParamMeta[]
     */
    public function getParams(){
        return $this->paramsMeta;
    }

    /**
     * 获取指定参数信息
     * @param $name
     * @return ParamMeta|null
     */
    public function getParam($name){
        foreach ($this->paramsMeta as $meta){
            if($meta->name == $name){
                return $meta;
            }
        }
        return null;
    }
    /**
     * @var ParamMeta[]
     */
    private $paramsMeta = [];


}