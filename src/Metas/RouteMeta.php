<?php

namespace PhpBoot\Metas;


use PhhBoot\Metas\ReturnMeta;

class RouteMeta
{
    /**
     * 文档
     * @var string
     */
    public $doc = "";

    /**
     * http method
     * @var string
     */
    public $method;

    /**
     * uri
     * @var string
     */
    public $uri;

    /**
     * 中间件 多个中间件用|拼接
     * @var string
     */
    public $middlewares;

    /**
     * 路由对应执行代码的类名
     * @var string
     */
    public $actionClass;
    /**
     * 路由对应执行代码的方法名
     * @var string
     */
    public $actionMethod;

    /**
     * 返回http响应和函数返回值的映射关系
     * http响应包括: content, (下期需要支持headers, headers.status, headers.cookies等)
     * 函数返回值包括: return的返回值, &引用变量的输出 @see ReturnMeta
     *
     * 示例1:
     * [
     *      '$.response.content'=>ReturnMeta('source'=>'$.return', 'type'=>'string', '这是个描述')
     * ]
     * 如果函数的返回值是['res'=>'ok']对应的响应为
     * ['res'=>'ok']
     *
     * 示例2:
     * [
     *      '$.response.content.code'=>ReturnMeta('source'=>200, 'type'=>'integer', 'doc'=>'这是个描述')
     *      '$.response.content.data'=>ReturnMeta('source'=>'$.return', 'type'=>'array', 'doc'=>'这是个描述')
     *      '$.response.content.len'=>ReturnMeta('source'=>'$.params.len', 'type'=>'integer', 'doc'=>'这是个描述')
     * ]
     *
     * 如果函数的返回值是['res'=>'ok'], 对应的响应为
     * [
     *      'code'=>200,
     *      'data'=>['res'=>'ok'],
     *      'len'=>xxx
     * ]
     * @var ReturnMeta[]
     */
    public $returns;

    /**
     * @var ParamMeta[]
     */
    public $params;

}