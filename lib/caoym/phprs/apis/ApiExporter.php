<?php
/***************************************************************************
 *
* Copyright (c) 2014 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: ApiExporter.php 58155 2015-01-05 14:45:30Z caoyangmin $
 *
 * @author caoyangmin(caoyangmin@baidu.com)
 *         @brief
 *         ApiExporter
 */
namespace caoym\phprs\apis;

use caoym\phprs\Invoker;
use caoym\util\AnnotationReader;
use Peekmo\JsonPath\JsonStore;
use caoym\util\HttpRouterEntries;
use caoym\util\Verify;

/**
 * 导出AP描述信息
 * @author caoym
 * @path("/")
 */
class ApiExporter
{
    
    /**
     * 导出API信息
     * @route({"GET", "/apis.json"})
     * @return({"header", "Content-Type: application/json; charset=UTF-8"})
     * @return({"body"}) array
     */
    public function exportJson()
    {
        $apis = array();
        // 导出hook信息
        foreach ($this->router->getHooks() as $hooks) {
            foreach ($hooks as $method => $hook) {
                $entries = $hook->export();
                foreach ($entries as $entry) {
                    list ($uri, $invoker) = $entry;
                    $info['type'] = 'hook';
                    $info['uri'] = array(
                        $method,
                        $uri
                    );
                    $info = array_merge($info, $this->getInvokerInfo($method,$uri, $invoker));
                    $apis[$invoker->getClassName()]['apis'][] = $info;
                }
            }
        }
        // 导出api信息
        foreach ($this->router->getRoutes() as $method => $route) {
            $entries = $route->export();
            foreach ($entries as $entry) {
                list ($uri, $invoker) = $entry;
                $info['type'] = 'api';
                $info['uri'] = array(
                    $method,
                    $uri,
                );
                $info = array_merge($info, $this->getInvokerInfo($method,$uri,$invoker));
                $apis[$invoker->getClassName()]['apis'][] = $info;
            }
        }
        
        foreach ($apis as $class_name => &$info) {
            $ann = new \ReflectionClass($class_name);
            $apis[$class_name]['doc'] = $this->getDocText($ann->getDocComment());
            //排序, 便于阅读
            usort($info['apis'], function($lh, $rh){
                return strcmp($rh['uri'][1].$rh['uri'][0], $lh['uri'][1].$lh['uri'][0] );
            });
        }
        return $apis;
    }

    private static $css = <<<EOT
<style type="text/css">
pre {border: 1px solid #bbb; padding: 10px;}
</style>
EOT;

    /**
     * 导出API信息
     * 简陋的html形式
     *
     * @param Router $router
     * @route({"GET", "/apis"})
     * @return ({"header", "Content-Type: text/html; charset=UTF-8"})
     */
    public function exportMainHtml()
    {
        $info = $this->exportJson();
        
        $body = '<html>';
        $body .= '<body>';
        $body = '<ol>';
        foreach ($info as $class_name => $apis) {
            $body .= "<li><a href=./" . str_replace('\\', '/', $class_name) . ">$class_name</a></li>";
            $body .= '<p>';
            $body .= nl2br(htmlentities($apis['doc']));
            $body .= '</p>';
        }
        $body .= '</ol>';
        $body .= '</body>';
        $body .= '</html>';
        return $body;
    }

    /**
     * 导出API信息
     * 简陋的html形式
     *
     * @route({"GET", "/apis/*"})
     * 
     * @param({"class_name", "$.path[1:]"})
     * @return ({"header", "Content-Type: text/html; charset=UTF-8"})
     */
    public function exportApiHtml($class_name)
    {
        if (is_array($class_name)) {
            $class_name = implode('\\', $class_name);
        }
        // TODO: html+js重写吧
        $body = self::$css;
        $body .= '<html>';
        $body .= '<body>';
        $info = $this->exportJson();
        $apis = $info[$class_name];
        // 类名
        $body .= '<h1>';
        $body .= htmlentities($class_name);
        $body .= '</h1>';
        
        $body .= '<p>';
        $body .= nl2br(htmlentities($apis['doc']));
        $body .= '</p>';
        
        $body .= '<ol>';
        // 接口
        foreach ($apis['apis'] as $api) {
            $body .= '<h2>';
            $body .= '<li>';
            $body .= htmlentities($api['uri'][0] . ' ' . $api['uri'][1]);
            $body .= '</li>';
            $body .= '</h2>';
            // 说明
            $body .= '<p>';
            $body .= nl2br(htmlentities($api['doc']));
            $body .= '</p>';
            
            // 请求
            list ($sample, $doc) = $this->createRequestDoc($api);
            $body .= '<h3>>>Request</h3>';
            $body .= '<pre>';
            $body .= $sample;
            $body .= '</pre>';
            
            $body .= '<p>';
            $body .= nl2br(htmlentities($doc));
            $body .= '</p>';
            
            // 响应
            
            list ($sample, $doc) = $this->createResponseDoc($api, $api['type'] ==='api');
            $body .= '<h3>>>Response(OK)</h3>';
            $body .= '<pre>';
            $body .= $sample;
            $body .= '</pre>';
            
            $body .= '<p>';
            $body .= nl2br(htmlentities($doc));
            $body .= '</p>';
            
            // 异常
            $fails = $this->createExceptionDoc($api);
            
            foreach ($fails as $name => $info) {
                $body .= '<h3>>>Response (Fail: ' . $name . ')</h3>';
                $body .= '<pre>';
                $body .= $info[0];
                $body .= '</pre>';
                
                $body .= '<p>';
                $body .= nl2br(htmlentities($info[1]));
                $body .= '</p>';
            }
            
            $body .= '<h3>>>Response (Fail: unknown)</h3>';
            $body .= '<pre>';
            $body .= "HTTP/1.1 500 Internal Server Error\r\n\r\n";
            $body .= '</pre>';

        }
        $body .= '</ol>';
        
        $body .= '</body>';
        $body .= '</html>';
        return $body;
    }

    /**
     * 生成请求的示例和说明
     *
     * @param array $api            
     * @return array [sample, doc]
     */
    private function createRequestDoc($api)
    {
        //TODO: 需要处理特殊情况: 输入被绑定在多个参数, 或者输入的不同重叠区域被绑定到不同参数时 
        $docs = '';
        // 提取参数
        $params = new JsonStore(array());
        foreach ($api['params'] as $name => $param) {
            $ori = $params->get($param['value']);
            if (count($ori) !== 0) { // 现在不支持同一个变量多个地方引用
                continue;
            }
            $info = new \ArrayObject(array(
                $name,
                $param,
            ));
            $params->set($param['value'], $info);
        }
        $params = $params->toArray();
        // 路由中指定的路径
        $route_path = HttpRouterEntries::stringToPath($api['uri'][1]); // 这是绝对路径
        $path = $api['uri'][0];
        // 路径拼到示例中
        if (isset($params['path'])) {
            $req_path = $params['path']; // 请求中使用的路径, 这是相对路径
            $offest = count(HttpRouterEntries::stringToPath($api['root'])); // 相对于绝对路径的偏移
            
            if (is_array($req_path)) { // 参数只是路径的一部分
                if(count($req_path)>0){
                    $end = max(array_keys($req_path));
                    Verify::isTrue($end <128, "too long path with length $end");
                    for ($i = 0; $i <= $end; $i ++) {
                        if (isset($req_path[$i])) {
                            list ($arg_name, $arg_info) = $req_path[$i];
                            if(isset($route_path[$i + $offest]) && $route_path[$i + $offest] !=='*'){
                                //忽略固定的路径
                            }else{
                                $route_path[$i + $offest] = "[$arg_name]";
                                $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
                            }
                        } else {
                            if (! isset($route_path[$i + $offest])) {
                                $route_path[$i + $offest] = '*';
                            }
                        }
                    }
                }
            } else { // 参数整个路径
                list ($arg_name, $arg_info) = $req_path;
                $route_path[$offest] = "[$arg_name]";
                $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
            }
            
            unset($params['path']);
        }
        $path .= ' /';
        $path .= implode('/', $route_path);
        // querystring
        if (isset($params['_GET'])) {
            $get = $params['_GET'];
            if(is_array($get)){
                $first = true;
                foreach ($get as $name => $value) {
                    list ($arg_name, $arg_info) = $value;
                    if ($first) {
                        $path = $path . '?';
                        $first = false;
                    } else {
                        $path = $path . '&';
                    }
                    $path = "$path$name=[$arg_name]";
                    $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
                }
            }else{
                // 参数整个_GET
                list ($arg_name, $arg_info) = $get;
                $path = "$path?[$arg_name]";
                $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
            }
            unset($params['_GET']);
        }
        $path .= " HTTP/1.1\r\n";
        
        // header
        $header = '';
        if (isset($params['header'])) {
            $headers = $params['header'];
            $first = true;
            foreach ($headers as $header_name => $value) {
                
                //if (substr_compare($name, 'HTTP_X_', 0, 7) !== 0) {
                //    continue;
                //}
                //$words = explode('_', substr($name, 7));
                //$header_name = '';
                //foreach ($words as $k => $word) {
                //    $words[$k] = ucwords(strtolower($word));
                //}
                //$header_name = implode('-', $words);
                list ($arg_name, $arg_info) = $value;
                $header = "$header$header_name: [$arg_name]\r\n";
                $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
                unset($params['_SERVER'][$name]);
            }
        }
        
        // cookie
        $header = '';
        if (isset($params['_COOKIE'])) {
            $cookies = $params['_COOKIE'];
            $first = true;
            $header = $header."Cookie: ";
            foreach ($cookies as $cookie_name => $value) {
                list ($arg_name, $arg_info) = $value;
                $header = "$header$cookie_name=[$arg_name];";
                $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
            }
            $header .= "\r\n";
        }
        
        // body
        $body = '';
        if (isset($params['_POST'])) {
            $post = $params['_POST'];
            $first = true;
            if(is_array($post)){
                foreach ($post as $name => $value) {
                    list ($arg_name, $arg_info) = $value;
                    if ($first) {
                        $first = false;
                    } else {
                        $body = $body . '&';
                    }
                    $body = "$body$name=[$arg_name]";
                    $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
                }
            }else{
                // 参数整个_POST
                list ($arg_name, $arg_info) = $post;
                $body = "{$body}[$arg_name]";
                $docs = "$docs$arg_name:\r\n {$arg_info['doc']}\r\n\r\n";
            }
            unset($params['_POST']);
        }
        
        if (isset($params['_FILES'])) {
            $files = $params['_FILES'];
            if(is_array($files)){
                foreach ($files as $name => $value) {
                    //TODO: 这里假设只有一个文件上传
                    list ($arg_name, $arg_info) = $this->searchArgInfo($value);
                    $docs = "$docs$name:\r\n {$arg_info['doc']}\r\n\r\n";
                }
            }
            unset($params['_POST']);
        }
        
        $sample = $path . $header . "\r\n" . $body;
        return array(
            $sample,
            $docs,
        );
    }
    private function searchArgInfo($value){
        if(is_object($value)){
            return $value;
        }
        return $this->searchArgInfo(array_values($value)[0]);
    }
    /**
     * 生成响应的示例和说明
     *
     * @param array $api            
     * @return array [sample, doc]
     */
    private function createResponseDoc($api, $default_return=true)
    {
        // 'name' => $fun_name,
        // 'args' => $args,
        // 'doc' => $anns['return'][$id]['doc']
        $status  = '';
        if($default_return){
            $status = "HTTP/1.1 200 OK\r\n";
        }
        
        $header = '';
        $doc = '';
        $body = '';
        foreach ($api['returns'] as $return) {
            $info = $this->getResponseInfo($return);
            if (isset($info['status'])) {
                $status = $info['status'];
            }
            if (isset($info['header'])) {
                $header .= $info['header'];
            }
            if (isset($info['body'])) {
                $body .= $info['body'];
            }
            if (isset($info['doc']) && $info['doc']) {
                $doc .= $info['doc'];
            }
        }
        $sample = $status . $header . "\r\n" . $body;
        return array(
            $sample,
            $doc,
        );
    }

    /**
     * 生成响应的示例和说明
     *
     * @param array $api            
     * @return array [sample, doc]
     */
    private function createExceptionDoc($api)
    {
        // 'name' => $fun_name,
        // 'args' => $args,
        // 'doc' => $anns['return'][$id]['doc']
        $res = array();
        foreach ($api['throws'] as $name => $throws) {
            $status = "HTTP/1.1 500 Internal Server Error\r\n";
            $header = '';
            $doc = '';
            $body = '';
            
            foreach ($throws as $throw) {
                $info = $this->getResponseInfo($throw);
                if (isset($info['status'])) {
                    $status = $info['status'];
                }
                if (isset($info['header'])) {
                    $header .= $info['header'];
                }
                if (isset($info['body'])) {
                    if(is_array($info['body'])){
                        $body .= json_encode($info['body']);
                    }else{
                        $body .= $info['body'];
                    }
                }
                if (isset($info['doc']) && $info['doc']) {
                    $doc .= $info['doc'];
                }
            }
            $sample = $status . $header . "\r\n" . $body;
            $res[$name] = array(
                $sample,
                $doc,
            );
        }
       
        return $res;
    }

    /**
     * 获取单个响应的示例和说明
     *
     * @param array $api            
     * @return array [sample, doc]
     */
    private function getResponseInfo($return)
    {
        $res = array();
        if ($return['name'] === 'status') {
            $arg = $return['args'][0];
            $value = $arg['value'];
            if ($arg['is_const']) {
                $res['status'] = "HTTP/1.1 $value\r\n";
            } else {
                $res['status'] = "HTTP/1.1 [$value]\r\n";
            }
            if ($return['doc']) {
                $res['doc'] = "$value:\r\n {$return['doc']}\r\n\r\n";
            }
        } elseif($return['name'] === 'res'){
            $arg = $return['args'][0];
            $value = $arg['value'];
            if ($arg['is_const']) {
                $res['status'] = "HTTP/1.1 $value\r\n";
            } else {
                $res['status'] = "HTTP/1.1 [$value]\r\n";
            }
            if ($return['doc']) {
                $res['doc'] = "$value:\r\n {$return['doc']}\r\n\r\n";
            }
            
            $arg = $return['args'][1];
            $value = $arg['value'];
            if ($arg['is_const']) {
                $res['body'] = $value;
            } else {
                if($value){
                    $res['body'] = "[$value]";
                }else{
                    $res['body'] = "[return]";
                }
            
            }
            if ($return['doc']) {
                if(is_array($value)){
                    $value = json_encode($value);
                }
                $res['doc'] = "return $value:\r\n {$return['doc']}\r\n\r\n";
            }
            
        }elseif($return['name'] === 'header') {
            $arg = $return['args'][0];
            $value = $arg['value'];
            if ($arg['is_const']) {
                $res['header'] .= "$value \r\n";
            } else {
                $res['header'] .= "[$value] \r\n";
            }
            if ($return['doc']) {
                $res['doc'] = "$value:\r\n {$return['doc']}\r\n\r\n";
            }
        } elseif ($return['name'] === 'cookie') {
            $args = $return['args'];
            foreach ($args as $k => &$arg) {
                if (! $arg['is_const']) {
                    $value = $arg['value'];
                    $arg['value'] = "[$value]";
                }
            }
            if ($return['doc']) {
                $res['doc'] = "cookie {$args[0]['value']}:\r\n {$return['doc']}\r\n\r\n";
            }
            
            $res['header'] = 'Set-Cookie: ' . $args[0]['value'] . '=' . $args[1]['value'] 
            . (empty($args[2]['value']) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', strtotime($args[2]['value'])) . ' GMT') 
            . (empty($args[3]['value']) ? '' : '; path=' . $args[3]['value']) 
            . (empty($args[4]['value']) ? '' : '; domain=' . $args[4]['value']) 
            . (empty($args[5]['value']) ? '' : '; secure') 
            . (empty($args[6]['value']) ? '' : '; HttpOnly');
            $res['header'] .= "\r\n";
        } elseif ($return['name'] === 'body') {
            $arg = $return['args'][0];
            $value = $arg['value'];
            if ($arg['is_const']) {
                $res['body'] = $value;
            } else {
                if($value){
                    $res['body'] = "[$value]";
                }else{
                    $res['body'] = "[return]";
                }
                
            }
            if ($return['doc']) {
                if(is_array($value)){
                    $value = json_encode($value);
                }
                $res['doc'] = "return $value:\r\n {$return['doc']}\r\n\r\n";
            }
        }
        return $res;
    }

    /**
     * 遍历数组, 子数组
     *
     * @param unknown $arr            
     */
    static function walkTree(&$arr, $visitor)
    {
        foreach ($arr as $k => &$v) {
            if (is_array($v)) {
                self::walkTree($v, $visitor);
            } else {
                $visitor($v);
            }
        }
    }

    /**
     * 获取invoker信息
     *
     * @param Invoker $invoker        
     * @param $method http方法
     * @param $uri http url
     * @return array
     */
    public function getInvokerInfo( $method,$uri, $invoker)
    {
        $res = array();
        $res['impl'] = $invoker->getMethodName();
        $ann = new AnnotationReader();
        $refl = new \ReflectionClass($invoker->getClassName());
        $mrefl = $refl->getMethod($invoker->getMethodName());
        $anns = $ann->getMethodAnnotations($mrefl, true);
        // 过滤无效的参数
        if(isset($anns['param'])){
            $anns['param'] = array_values(array_filter($anns['param'], function ($i)
            {
                return isset($i['value']);
            }));
        }
        if(isset($anns['return'])){
            $anns['return'] = array_values(array_filter($anns['return'], function ($i)
            {
                return isset($i['value']);
            }));
        }
        if(isset($anns['throws'])){
            $anns['throws'] = array_values(array_filter($anns['throws'], function ($i)
            {
                return isset($i['value']);
            }));
        }
        
        $res['doc'] = $this->getDocText($mrefl->getDocComment());
        //找到匹配的@route注释
        foreach ($anns['route'] as $route_doc){
            //TODO: 同时精确匹配和通配符匹配时, 怎么处理
            if(isset($route_doc['value'])){
                list($m, $u) = $route_doc['value'];
                $full_url = $invoker->getContainer()->path.'/'.$u;
                $full_url = HttpRouterEntries::stringToPath($full_url);
                if($full_url == HttpRouterEntries::stringToPath($uri) &&
                    $m === $method){
                    $text = $this->getDocText($route_doc['desc']);
                    if(!empty($text)){
                        $res['doc'] .= "\r\n";
                        $res['doc'] .= $text;
                    }
                    break;
                }
            }
        }
        $anns['route'];
        $res['root'] = '/';
        $res['path'] = $invoker->getContainer()->path;
        
        // 获取参数信息
        $res['params'] = array();
        foreach ($invoker->getParams()->getParams() as $param) {
            list ($is_const, $value, $info, $id) = $param;
            list ($name, $is_ref, $is_optional, $default) = $info;
            if (! $is_const) {
                $res['params'][$name] = array(
                    'value' => $value,
                    'is_ref' => $is_ref,
                    'is_optional' => $is_optional,
                    'default' => $default,
                    'doc' => $this->getDocText($anns['param'][$id]['desc']),
                );
            }
        }
        
        // 依赖只是特殊的参数
        $defaults = $refl->getDefaultProperties();
        foreach ($refl->getProperties() as $property) {
            foreach ($ann->getPropertyAnnotations($property, true) as $type => $value) {
                if ($type !== 'inject') {
                    continue;
                }
                $name = $property->getName();
                $value = $value[0];
                if (is_array($value['value'])) {
                    $src = $value['value']['src'];
                    if (isset($value['value']['optional']) && $value['value']['optional']) {
                        $is_optional = true;
                    }
                    if (isset($value['value']['default'])) {
                        $default = $value['value']['default'];
                    }
                } else {
                    $src = $value['value'];
                }
                
                if (substr($src, 0, 1) !== '$') {
                    continue;
                }
                if (array_key_exists($name, $defaults)) {
                    $is_optional = true;
                    $default = $defaults[$name];
                }
                
                $res['params'][$name] = array(
                    'value' => $src,
                    'is_ref' => false,
                    'is_optional' => $is_optional,
                    'default' => $default,
                    'doc' => $this->getDocText($value['desc']),
                );
            }
        }
        
        // 获取返回值信息
        $res['returns'] = array();
        foreach ($invoker->getReturns()->getParams() as $fun_name => $calls) {
            foreach ($calls as $id => $call) {
                $args = array();
                foreach ($call as $num => $arg) {
                    list ($is_const, $value, $pos, $info) = $arg;
                    list ($name, $is_ref, $is_optional, $default) = $info;
                    
                    $args[$num] = array(
                        'value' => $value,
                        'name' => $name,
                        'is_const' => $is_const,
                    );
                }
                
                $res['returns'][] = array(
                    'name' => $fun_name,
                    'args' => $args,
                    'doc' => $id===-1?null:$this->getDocText($anns['return'][$id]['desc']),
                );
            }
        }
        // 获取异常信息
        $res['throws'] = array();
        foreach ($invoker->getThrows()->getParams() as $exce_name => $throws) {
            // $res['throws'][$exce_name] = array();
            foreach ($throws as $fun_name => $calls) {
                foreach ($calls as $id => $call) {
                    $args = array();
                    if($call !== null){
                        foreach ($call as $num => $arg) {
                            $args[$num] = array(
                                'value' => $arg,
                                'name' => null,
                                'is_const' => true,
                            );
                        }
                    }
                    $res['throws'][$exce_name][] = array(
                        'name' => $fun_name,
                        'args' => $args,
                        'doc' => $this->getDocText($anns['throws'][$id]['desc']),
                    );
                }
            }
        }
        return $res;
    }

    /**
     * 去掉文档中的@标记和*号等, 保留可读文本
     * 
     * @param string $doc            
     * @return string
     */
    private function getDocText($doc)
    {
        $lines = explode("\n", $doc);
        $ignore = array(
            '@return',
            '@param',
            '@throws',
            '@route',
            '@path',
            '@cache',
        );
        $text = '';
        $fistline = true;
        $is_ignored = false;
        foreach ($lines as $num => $ori_line) {
            $line = trim($ori_line, "*/\r\n\t ");
            if($is_ignored){ 
                if(substr($line, 0, 1 ) !== '@'){
                    continue;
                }
            }
            $is_ignored = false;
            foreach ($ignore as $word) {
                if (strlen($line)>= strlen($word) && 0 === substr_compare($line, $word, 0, strlen($word))) {
                    $is_ignored = true;
                    break;
                }
            }
            if ($is_ignored) {
                continue;
            }
            if ($fistline) {
                $fistline = false;
            } else {
                $text .= "\r\n";
            }
            $text .= trim(trim($ori_line, " \t\r\n"), "*/");
        }
        $text = trim($text, "\r\n");
        return $text;
    }

    /**
     * @inject("$.router")
     */
    private $router;
}
