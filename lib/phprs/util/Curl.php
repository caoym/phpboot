<?php
namespace phprs\util;
class CurlResponse{
    
    public $headers=array();
    public $content;
    public $http_code;
    public $errno;
    public $errstr;
    public $status;
    public $content_type;
    
    public function isOk(){
        return $this->errno==0 && intval($this->http_code)>=200 && intval($this->http_code)<300;
    }
    public function message(){
        return "errno:{$this->errno}, errstr:{$this->errstr}, http_code:{$this->http_code}, content:".print_r($this->content,true);
    }
    public function handleHeader($ch, $header_line){
        if(count($this->headers) ==0){
            $this->status = trim(explode(' ', $header_line,2)[1]);
        }
        list($n,$v) = explode(':', $header_line)+array(null,null);
        if(strcasecmp(trim($n),'Content-Type')===0){
            $this->content_type = trim($v);
        }
        $this->headers[]=$header_line;
        return strlen($header_line);
    }
    /**
     * 从http返回的数据中分离header和body
     * @param string $data
     */
    public function parseReturnData($data){
        while(true){
            $lines = explode("\n", $data,  2);
            if(!$lines || count($lines) == 0){
                break;
            }
            if(trim($lines[0]) == ''){//空行,header 结束
                if (stristr($this->content_type, 'application/Json') !== false) {
                    $this->content = json_decode($lines[1], true);
                } else {
                    $this->content = $lines[1];
                }
                break;
            }
            $this->handleHeader(null, $lines[0]);
            if(count($lines) !=2){
                break;
            }
            $data = $lines[1];
        }
    }
}
class Curl
{
    public function __construct(){
        $this->ch = curl_init();
        if(!file_exists('/dev/null')){
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'NUL'); 
        }else{
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, '/dev/null');
        }
        
    }
    public function reset(){
        curl_close($this->ch);
        $this->ch = curl_init();
        if(!file_exists('/dev/null')){
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'NUL');
        }else{
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, '/dev/null');
        }
    }
    public function __destruct(){
        curl_close($this->ch);
    }
    public function GET($url, $headers=null,$followLoc=true){
        return $this->execCurl($url, __FUNCTION__, null, $headers,$followLoc);
    }
    public function POST($url, $content, $headers=null,$followLoc=true){
        return $this->execCurl($url, __FUNCTION__, $content, $headers,$followLoc);
    }
    public function PUT($url, $content, $headers=null,$followLoc=true){
        return $this->execCurl($url, __FUNCTION__, $content, $headers,$followLoc);
    }

    /**
     * @param $url
     * @param string $method
     * @param null $content
     * @param null $headers
     * @param bool $followLoc
     * @return CurlResponse
     */
    private function execCurl($url, $method='GET', $content=null,  $headers=null,$followLoc=true){
        $res = new CurlResponse();
        
		if(isset($method)){
		    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		curl_setopt($this->ch, CURLOPT_URL,$url);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
		if(!empty($headers)){
		    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION, $followLoc?1:0);//支持跳转

		//curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($res,'handleHeader')); // handle received headers
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		if(!empty($content)){
		    $content_type = '';
		    // 取content-type
		    foreach ($headers as $h){
		        list($n,$v) = explode(':',$h)+array(null,null);
		        if(strcasecmp(trim($n),'Content-Type')===0){
		            $content_type = trim($v);
		            break;
		        }
		    }
		    
		    if(is_array($content) && $content_type == 'application/json'){
		        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($content));
		    }else if($content_type == 'multipart/form-data'){
		        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
		    }else{
		        if(is_array($content) ){
		            curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($content));
		        }else{
		            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
		        }
			    
		    }
		}
		$response = curl_exec($this->ch);
        
        $res->http_code =  curl_getinfo($this->ch,CURLINFO_HTTP_CODE);
        $res->errno = curl_errno($this->ch);
        $res->errstr = curl_error($this->ch);
        $res->parseReturnData($response);

		return $res;
    }

    /**
     * @var int
     * request time out in second
     */
    public $timeout = 60;
    /** curl handle */
    private $ch;
  
}