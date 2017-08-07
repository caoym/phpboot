<?php

namespace PhpBoot\Controller;


use Symfony\Component\HttpFoundation\Response;

class ResponseRenderer
{
    /**
     * @param array $output
     * @return string
     */
    public function render(array $output)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        foreach ($output as $key=>$value){
            //TODO 支持自定义格式输出
            //TODO 支持更多的输出目标
            if($key == 'content'){
                //if(is_array($value) || is_object($value)){
                    $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //}
                $response->setContent($value);
            }elseif($key == 'headers'){
                foreach ($value as $k=>$v){
                    $response->headers->set($k, $v);
                }
            }else{
                \PhpBoot\abort(new \UnexpectedValueException("Unexpected output target $key"));
            }

        }
        return $response;
    }
}