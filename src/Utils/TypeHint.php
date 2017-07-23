<?php

namespace PhpBoot\Utils;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;

class TypeHint
{
    /**
     * 标准化类型
     * 1. 基本类型, 转换成规范的拼写, 如double -> float, integer -> int
     * 2. 对象类型, 补全namespace
     * @param string $type 需要标准化的字符串
     * @param  string $contextClass 当前上下文所在的类,一般传__CLASS__, 用于扫描当前文件的use信息, 以便拼上namespace
     */
    static function normalize($type, $contextClass=null){
        $resolver = new TypeResolver();
        $context = null;
        if($contextClass){
            //TODO 优化性能
            $contextFactory = new ContextFactory();
            $context = $contextFactory->createFromReflector(new \ReflectionClass($contextClass));
        }
        $type = $resolver->resolve($type, $context);
        $type = ltrim($type, '\\');
        return (string)$type;
    }
    /**
     * 是否是基本类型
     * @param string $type
     */
    static function isScalarType($type){
        return in_array($type, [
            'bool',
            'int',
            'float',
            'string'
        ]);
    }

    /**
     * @param $type
     * @return bool
     */
    static function isArray($type){
        return ($type == 'array' || substr($type, -2) == '[]');
    }

    /**
     * 获取数组的类型
     * @param $type
     * @return string|null
     */
    static function getArrayType($type){
        self::isArray($type) or \PhpBoot\abort(new \InvalidArgumentException("$type is not array"));
        if($type == 'array') {
            return 'mixed';
        }else{
            return substr($type,0,-2);
        }
    }
}