<?php
/***************************************************************************
 *
* Copyright (c) 2013 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: HttpRouterEntries.php 58820 2015-01-16 16:29:33Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief HttpRouterEntries
 */
namespace caoym\util;
/**
 * 记录路由信息
 * @author caoym
 * 针对 path?arg1=x&argy=y的形式url进行路由
 * 搜索匹配的规则时,如果有多条规则匹配,则返回路径最深的(或者querystring最多的)规则
 * 比如规则 
 * "/api" => service1,
 * "/api?arg1=x" => service2,
 * 查询"/api?arg1=x&arg2=y" 找到service2
 */
class HttpRouterEntries{
	function __construct(){
		$this->routes = new Tree();
	}
	/**
	 * 
	 * @param string $q
	 * @param string $matched_path
	 * @return found object
	 */
	function find($q,&$matched_path=null){
	    list($path,$param) = explode('?', $q)+array( null,null );
	    $paths = self::stringToPath($path);
	    $params = is_null($param)?null:explode('&', $param);
	    return $this->findByArray($paths, $params, $matched_path);
	}
    /**
     * 查找url对应的实体
     * @param string $paths
     * @param string $params
     * @param array $matched_path 已匹配的路径
     * @return mixed|null
     */
	function findByArray($paths,$params, &$matched_path =null){
		$paths = array_filter($paths,function ($i){return $i!== '/' &&!empty($i);});
		array_unshift($paths, '/');
		$route=null;
		$valid_path=null;//最深一个不为空的路径
		$visited = 0;
		$geted=0;
		$walked_path=array();
		//找到匹配的最深路径，/a/b/c匹配的路径可以是/,/a,/a/c,/a/b/c,但最深的是/a/b/c
		$this->routes->visit($paths,function ($node,$value)use(&$valid_path,&$route,&$visited,&$geted,&$walked_path){
			if(!is_null($value)) {
				$route=$value;
				if(!($route instanceof \caoym\util\Tree)) {
				    $valid_path=$value;
				}
				$geted = $visited;
				
			}
			$walked_path[]=$node;
			$visited ++;
			return true;
		});
		if(is_null($route)){
		    return null;
		}
		if($matched_path !==null){
		    $matched_path = $walked_path;
		}
		//如果匹配完整的路径, 则还需要匹配querystring参数
		if(count($paths) == $geted+1){
			if($route instanceof \caoym\util\Tree){
				//条件要求有querystring，所以请求也需要有querystring
				if(empty($params)){
				    return $valid_path;
				}
				$found = $this->findParams($route, $params);
				return is_null($found)?$valid_path:$found ;
			}else{
				return $route;
			}
		}else{//如果不匹配全路径，则只能处理没有querystring的条件
			if($route instanceof \caoym\util\Tree) {
			    return $valid_path;
			}
			return $route;
		}
		return null;
	}
	/**
	 * 查找querystring对应的实体
	 * querystring 参数匹配条件在tree中的存储方式如下
	 * 
	 * 1.c=xxx&f=xxx&g=xxx
	 * 2.c=xxx&e=xxx&f-xxx
	 * 3.b=xxx&c=xxx
	 * (经过排序的数据结构)
	 *  c=xxx -> f=xxx -> g=xxx
	 *            \->e=xxx->f=xxx
	 *            b=xxx ->d=xxx
	 * 当收到请求 b=xxx&d=xxx&a=xxx&c=xxx时
	 * 先拆成数组再排序=>   a=xxx,b=xxx,c=xxx,d=xxx
	 * 先找 a=xxx（根据当前的数据结构，只可能是有一个或者没有，不可能存在多个），没找到则跳过
	 * 找b=xxx，有，则在b=xxx节点下找c=xxx，没找到则跳过
	 * 在b=xxx节点下找d=xxx,找到,且 b=xxx ->d=xxx是完整的规则、请求的params也已处理完，则命中此规则
	 * @param Tree $root 根节点
	 * @param $params 需要查找的节点名
	 * @return mixed|null
	 */
	private function findParams($root,$params){
		
		//排序，使参数与顺序无关
		sort($params,SORT_STRING);
		$params = array_filter($params,function ($i){return !empty($i);});
		
		$find_step = array();
		$matching = null;
		foreach ($params as $param){
			$find_step[]=$param;
			$node = $root->findNode($find_step);
			if($node === null) {
				array_pop($find_step);//换一个接着找
			}else{
				if(array_key_exists('value', $node)){
					$matching = $node['value'];
				}
			}
		}
		return $matching;
	}
	/**
	 * 增加一条规则
	 * @param string $query 请求的url形式
	 * @param mixed $e
	 * @return boolean 是否插入成功
	 */
	public function insert($query,$e){
	    list($path, $param) = explode('?', $query)+array(  null, null  );
	    $path = str_replace('\\', '/', $path);
	    $paths = explode('/', $path);
	    $route=null;
	    $params = null;
	    if($param !==null && $param !== ""){
	        $params = explode('&', $param);
	    }
	    return  $this->insertByArray($paths,$params,$e);
	}
	/**
	 * 增加一条路由规则
	 * @param array $paths
	 * @param array $params
	 * @param mixed $e
	 * @return boolean
	 */
	public function insertByArray($paths,$params,$e){
		//生成树形的路由表，便于快速查找,
		// 如 规则
		//  "/b/a?z=1&y=2" => service1
		//  "/b/a?z=1&x=2" => service2
		//  对应的 规则结构是
		// path ->  a+
		//           |-b+
		// param ->    |-x+
		//             	  |-z   => service2
		//             |-y+
		//                |-z   => service1
		//
		//
		//
		$paths = array_filter($paths, function ($i){return !empty($i);});
		array_unshift($paths, '/');
		if(empty($paths)){
		    $paths = array('');
		}
		if( $params !== null){
			//排序，使参数与顺序无关
			sort($params, SORT_STRING);
			$params = array_filter($params,function ($i){return !empty($i);});
			$node = $this->routes->findNode($paths, true, true);
			if($node && $node['value'] instanceof  \caoym\util\Tree){
			    $route = $node['value'];
			    return $route->insert($params, $e,false);
			}else{
			    $route = new Tree();
			    $route->insert($params, $e,false);
			    return $this->routes->insert($paths, $route,false);
			}
		}else{
			return $this->routes->insert($paths, $e ,false);
		}
	}
	/**
	 * 树打平输出成数组
	 * @return array
	 */
	public function export(){
	    $ori = $this->routes->export();
	    $res = array();
	    foreach ($ori as $v){
	        list($path, $value) = $v;
	        if($value instanceof \caoym\util\Tree){
                $querys = $value->export(); //提取querystring
                $path_str = self::pathToString($path);
                foreach ($querys as $query){
                    list($param, $value) = $query;
                    $res[]=array($path_str.'?'.implode('&',$param), $value);
                }
	        }else{
	            $res[]=array(self::pathToString($path), $value);
	        } 
	    }
	    return $res;
	}
	/**
	 * 字符串路转径数组路
	 * @param string $path
	 * @return array
	 */
	static public function stringToPath($path){
	    $path = str_replace('\\', '/', $path);
	    $paths = explode('/', $path);
	    $paths = array_filter($paths,function ($i){return !empty($i);});
	    return array_values($paths);
	}
	/**
	 * 数组路径转字符串路径
	 * @param array $path
	 * @return string 用/连接的字符串
	 */
	static public function pathToString($path){
	    $append=function (&$str, $v){
	        //连续的/忽略
	        if(strlen($str) !==0 && substr_compare($str, '/', strlen($str)-1) ===0 && $v==='/'){
	            return;
	        }
	        //两个项目间加/
	        if(strlen($str) !==0 && substr_compare($str, '/', strlen($str)-1) !==0 && $v!=='/'){
	            $str =$str.'/'.$v;
	        }else{
	            $str.=$v;
	        }        
	    };
	    $str = '';
	    foreach ($path as $i){
	        $append($str, $i);
	    }
	    return $str;
	}
	private $routes;
}