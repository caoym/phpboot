<?php
/**
 * $Id: Request.php 63900 2015-05-19 07:09:58Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief Request
 */
namespace phprs;
use phprs\util\Verify;
use Peekmo\JsonPath\JsonStore;
use phprs\util\exceptions\BadRequest;
/**
 * @author caoym
 * http请求包装
 * 允许使用jsonpath表达式获取http请求信息
 * 如
 * req['$._GET.param1']
 * 
 * TODO: 使用更友好的变量名替代直接用php的全局变量, 如$.uri.query 替代$._GET, $.body 替代$._POST
 */
class Request implements \ArrayAccess
{
    //TODO: 在API实现中直接使用$_GET等全局变量是不推荐的, 应该给予警告提醒
    /**
     * 
     * @param array $data
     */
    function __construct($data=null,$url_begin=0){
        if($data === null){
            $data = $GLOBALS;
            $data['header'] = $this->getAllHeaders();
        }
        $this->url_begin = $url_begin;
        //支持json请求(Content-Type: application/json)
        $contentType = null;
        if(isset($data['header']['Content-Type'])){
            $contentType = $data['header']['Content-Type'];
            list($contentType, ) = explode(';', $contentType)+array(null,null);
            if($contentType == 'application/json'){
                $post = file_get_contents('php://input');
                if($post != ''){
					$post = json_decode($post, true);
					Verify::isTrue(is_array($post), new BadRequest('post unjson data with application/json type'));
                    $data['_POST'] = $post;
                    if(!isset($data['_REQUEST'])){
                        $data['_REQUEST'] = [];
                    }
                    $data['_REQUEST'] = array_merge($data['_POST'], $data['_REQUEST'] );
                }
            }
            elseif($contentType == 'text/plain'){
                $data['_POST'] = file_get_contents('php://input');
            }
        }
        //TODO: 支持put请求
        if(isset($data['_SERVER']['REQUEST_METHOD']) &&  'PUT' == $data['_SERVER']['REQUEST_METHOD']){
           if($contentType == 'application/x-www-form-urlencoded'){
               $queryString = file_get_contents('php://input');
               $query = array();
               parse_str($queryString, $query);
               $data['_POST'] = $query;
           }
        }
      
        $full_path = $data['_SERVER']['REQUEST_URI'];
        Verify::isTrue($full_path, '$._SERVER.REQUEST_URI not found' );
        list($full_path,) = explode('?', $full_path);
        
        $paths = explode('/', $full_path);
        $paths = array_filter($paths,function ($i){return $i !== '';});
        $paths = array_slice($paths, $this->url_begin);
        $data['path'] = $paths;
        $this->data = new JsonStore($data);
    }
    /**
     * 获取http请求的所有header信息
     * @return array
     */
    function getAllHeaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else if ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
            }
        }
        return $headers;
    }
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset){
        return count($this->data->get($offset)) !==0;
    }
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        $res = $this->data->get($offset);
        if(!isset($res[0])){
            trigger_error("$offset not exist");
        }
        return $res[0];
    }
    /**
     * @param string $expr  jsonpath 表达式
     * @param $create 是否找不到就创建
     * @return [found, is succeed]
     */
    public function find($expr, $create=false){
        $res= $this->data->get($expr, false, $create);
        if(count($res) ===0  ){
            return array(null,false);
        }else if(count($res)  ===1){
            return array(&$res[0], true);
        }else{
            return array(&$res, true);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        Verify::isTrue($this->data->set($offset, $value));
    }
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        Verify::isTrue(false, 'NOT IMPL');
    }
   /**
    * 取数包含所有请求信息的数组
    * @return multitype:
    */
    public function toArray(){
        return $this->data->toArray();
    }
    private $data; 
    private $url_begin;
}
