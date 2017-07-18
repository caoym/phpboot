<?php

namespace PhpBoot\Controller;

use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Utils\ArrayAdaptor;
use PhpBoot\Utils\ArrayHelper;
use PhpBoot\Utils\ObjectAccess;
use Symfony\Component\HttpFoundation\Response;

class ResponseHandler
{
    /**
     * 设置输出映射
     * @param $target
     * @param ReturnMeta $src
     */
    public function setMapping($target, ReturnMeta $src)
    {
        $this->mappings[$target] = $src;
    }

    /**
     * @param $target
     * @return ReturnMeta
     */
    public function eraseMapping($target)
    {
        $ori = $this->mappings[$target];
        unset($this->mappings[$target]);
        return $ori;
    }

    /**
     * @param $target
     * @return ReturnMeta
     */
    public function getMapping($target)
    {
        if(!array_key_exists($target, $this->mappings)){
            return null;
        }
        return $this->mappings[$target];
    }


    public function handle($return, $params)
    {
        $input = [
            'return'=>$return,
            'params'=>$params
        ];

        $mappings = $this->getMappings();
        if($return instanceof Response){ //直接返回Response时, 对return不再做映射
            return $return;
        }

        $response = new Response();
        $output = new ArrayAdaptor($response);

        foreach($mappings as $key=>$map){
            $val = \JmesPath\search($map->source, $input);
            if(substr($key, 0, strlen('response.')) == 'response.'){
                $key = substr($key, strlen('response.'));
            }
            ArrayHelper::set($output, $key, $val);
        }
        return $response;
    }
    /**
     * @return ReturnMeta[]
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
     * 如果函数的返回值是['res'=>'ok']对应的响应为
     * [
     *      'code'=>200,
     *      'data'=>['res'=>'ok'],
     *      'len'=>xxx
     * ]
     */
    public function getMappings()
    {
        return $this->mappings;
    }
    /**
     * @var array
     */
    private $mappings;
}