<?php
namespace Once\Controller;
use Once\Exceptions\AnnotationSyntaxExceptions;
use Once\Metas\ReturnMeta;
use Once\Utils\ObjectAccess;
use Once\Utils\Verify;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReturnHandler
 * @package Once\Container
 * 返回值处理
 */
class ReturnHandler
{

    /**
     * 设置输出映射
     * @param $target
     * @param ReturnMeta $src
     */
    public function setMapping($target, ReturnMeta $src){
        $this->mappings[$target] = $src;
    }

    /**
     * @param $target
     * @return ReturnMeta
     */
    public function eraseMapping($target){
        $ori = $this->mappings[$target];
        unset($this->mappings[$target]);
        return $ori;
    }

    /**
     * @param $target
     * @return ReturnMeta
     */
    public function getMapping($target){
        if(!array_has($this->mappings, $target)){
            return null;
        }
        return $this->mappings[$target];
    }


    /**
     * @param Context context
     * @param mixed|null $return
     * @param array $params
     */
    public function handle(Context $context, $return, $params){
        $input = [
            'return'=>$return,
            'params'=>$params
        ];

        $mapings = $this->getMappings();
        if($return instanceof Response){ //直接返回Resonse时, 对return不再做映射
            $context->setResponse($return);
            return;
            //$mapings = array_filter($mapings, function($v){return $v->source != '$.return';});
        }
        $output = [];
        $pOutput = new ObjectAccess($output);
        $pInput = new ObjectAccess($input);

        foreach($mapings as $key=>$map){
            if(substr($map->source,0,2) == '$.'){
                //TODO: 转json
                $pOutput->set($key, $pInput->get($map->source));
            }else{
                $pOutput->set($key, $map->source);
            }

        }
        if (count($output) == 0){
            return;
        }
        array_has($output, 'response') or Verify::fail(
            new AnnotationSyntaxExceptions("$.{$output[0]} is invalid for http output"));

        foreach ($output['response'] as $k=>$v){
            if ($k == 'content'){
                $context->getResponse()->setContent($v);
            }elseif ($k == 'status'){
                $context->getResponse()->setStatusCode($v);
            }else{
                //TODO * 支持输出header, cookie
                Verify::fail(
                    new AnnotationSyntaxExceptions("$.response.$k is invalid for http output"));
            }
        }
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