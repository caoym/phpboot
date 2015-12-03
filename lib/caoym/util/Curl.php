<?php
namespace caoym\util;
class CurlResponse{
    
    public $headers=array();
    public $content;
    public $http_code;
    public $errno;
    public $errstr;
    public $status;
    public function isOk(){
        return $this->errno==0 && intval($this->status)>=200 && intval($this->status)<300;
    }
    public function message(){
        return "errno:{$this->errno}, errstr:{$this->errstr}, http_code:{$this->http_code}, content:{$this->content}";
    }
    public function handleHeader($ch, $header_line){
        if(count($this->headers) ==0){
            $this->status = trim(explode(' ', $header_line,2)[1]);
        }
        $this->headers[]=$header_line;
        return strlen($header_line);
    }
}
class Curl
{
    public function GET($url, $headers=null){
        return $this->execCurl($url, __FUNCTION__, null, $headers);
    }
    public function POST($url, $content, $headers=null){
        return $this->execCurl($url, __FUNCTION__, $content, $headers);
    }
    public function PUT($url, $content, $headers=null){
        return $this->execCurl($url, __FUNCTION__, $content, $headers);
    }
    private function execCurl($url, $method='GET', $content=null,  $headers=null){
        $res = new CurlResponse();
        $ch = curl_init();
		if(isset($method)){
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if($headers !== null){
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);//支持跳转

		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($res,'handleHeader')); // handle received headers
		
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
		        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
		    }else if($content_type == 'multipart/form-data'){
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		    }else{
		        if(is_array($content) ){
		            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
		        }else{
		            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		        }
			    
		    }
		}
		$response = curl_exec($ch);
        
        $res->http_code =  curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $res->errno = curl_errno($ch);
        $res->errstr = curl_error($ch);
        $content_type = '';
        // 取content-type
        foreach ($res->headers as $h){
            list($n,$v) = explode(':',$h)+array(null,null);
            if(strcasecmp(trim($n),'Content-Type')===0){
                $content_type = trim($v);
                break;
            }
        }
        if (stristr($content_type, 'application/Json')!==false) {
            $res->content = json_decode($response, true);
        } else {
            $res->content = $response;
        }
        
		curl_close($ch);
		return $res;
    }
  
}
