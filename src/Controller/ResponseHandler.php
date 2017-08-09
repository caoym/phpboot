<?php

namespace PhpBoot\Controller;

use PhpBoot\Application;
use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Utils\ArrayAdaptor;
use PhpBoot\Utils\ArrayHelper;

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
        if(!isset($this->mappings[$target])){
            return null;
        }
        $ori = $this->mappings[$target];
        unset($this->mappings[$target]);
        return $ori;
    }

    /**
     * @param string $target
     * @return ReturnMeta
     */
    public function getMapping($target)
    {
        if(!array_key_exists($target, $this->mappings)){
            return null;
        }
        return $this->mappings[$target];
    }

    /**
     * @param string $source
     * @return array [string,ReturnMeta]
     */
    public function getMappingBySource($source)
    {
        foreach ($this->mappings as $k=>$v){
            if($v->source == $source){
                return [$k, $v];
            }
        }
        return [null,null];
    }


    /**
     * @param Application $app
     * @param $return
     * @param $params
     * @return Response
     */
    public function handle(Application $app, $return, $params)
    {
        $input = [
            'return'=>$return,
            'params'=>$params
        ];

        if($return instanceof Response){ //直接返回Response时, 对return不再做映射
            return $return;
        }
        $mappings = $this->getMappings();

        $output = [];
        foreach($mappings as $key=>$map){
            $val = \JmesPath\search($map->source, $input);
            if(substr($key, 0, strlen('response.')) == 'response.'){
                $key = substr($key, strlen('response.'));
            }
            ArrayHelper::set($output, $key, $val);
        }
        $renderer = $app->get(ResponseRenderer::class);
        return $renderer->render($output);
    }
    /**
     * @return ReturnMeta[]
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