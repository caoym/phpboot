<?php

/**
 * $Id: Tree.php 58820 2015-01-16 16:29:33Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief Tree
 */
namespace phprs\util;
class Tree {
	/**
	 * 插入一个节点
	 * 插入时允许使用通配符* 
	 * @param array $path
	 * @param unknown $value
	 * @param string $replace_exits 是否替换已存在的
	 * @param mixed $replaced 被替换的原始值
	 * @return boolean
	 */
	public function insert( $path, $value, $replace_exits=false, &$replaced=null){
		assert(count($path));
		$left = $path;
		$cur = array_shift($left);//剩余未遍历的
		if(!array_key_exists($cur,$this->arr)){
			$this->arr[$cur] = array();
		}
		$insert= false;
		$this->visitNode($path,function ($name,&$node)use(&$insert,&$left,$value,$replace_exits, &$replaced){
			$cur = array_shift($left);
			if($cur === null){
			    if(array_key_exists('value',$node)){//是否已存在
			        if(!$replace_exits){
			           return false;
			        }
			        $replaced = $node['value'];
			    }
			    $node['value'] = $value;
			    $insert = true;
				return false;//停止遍历
			}else{
				if(!array_key_exists('next',$node)){
					$node['next'] = array();
				}
				if(!array_key_exists($cur,$node['next'])){
					$node['next'][$cur]= array();
				}
			}
			return true;
		}, true);
		return $insert;
	}
	/**
	 * 删除一个路径下的所有节点
	 * @param array $path
	 * @return boolean
	 */
	public function erase(array $path){
		if(count($path)==0){
		    return false;
		}
		$size = count($path);
		$key = $path[$size-1];
		if($size ==1){
			if(array_key_exists($key, $this->arr)){
				unset($this->arr[$key]);
				return true;
			}
			return false;
		}
		$i = 0;
		$res = false;
		$full = $this->visitNode($path,function ($name,&$node)use(&$i,$size,$key,&$res){
			if(++$i == $size-1){
				if(isset($node['next']) && isset($node['next'][$key])){
					unset($node['next'][$key]);
					$res = true;
				}
				return false;
			}
			return true;
		}, true);
		return $res;
	}
	/**
	 * 遍历路径 
	 * @param array $path
	 * @return boolean 全部遍历完返回true，否则返回false
	 */
	public function visit( $path ,$vistor, $exact_match=false){
		return $this->visitNode($path,function ($name,$node)use($vistor){
			return $vistor($name,array_key_exists('value',$node)?$node['value']:null);
		}, $exact_match);
	}
	/**
	 * 查找指定路径的节点
	 * @param array $path
	 * @param boolean $exact_match 是否精确匹配,如果是,则通配符被认为与其他值不同
	 * @return 返回节点的值, 或者null
	 */
	public function find(array $path, $exact_match=false){
		$found = null;
		$full = $this->visitNode($path,function ($name,$node)use(&$found){
			$found = array_key_exists('value',$node)?$node['value']:null;
			return true;
		}, $exact_match);
		return $full?$found:null;
	}
	/**
	 * 查找指定路径的节点
	 * @param array $path
	 * @param boolean $exact_match 是否精确匹配,如果是,则通配符被认为与其他值不同
	 * @param boolean $all_req_paths 是否要求查询路径的所有元素都必须遍历到
	 * @param boolean $all_paths 是否
	 * @return 返回节点的, 或者null
	 */
	public function findNode(array $path, $exact_match=false, $all_req_paths=false){
		$found = null;
		$full = $this->visitNode($path,function ($name,$node)use(&$found){
			$found = $node;
			return true;
		}, $exact_match, $all_req_paths);
		return $full?$found:null;
	}
	/**
	 * 遍历路径
	 * @param array $path
	 * @param boolean $exact_match 是否精确匹配,如果是,则通配符被认为与其他值不同
	 * @param boolean $all_req_paths 是否要求查询路径的所有元素都必须遍历到
	 * @return boolean 变量完成返回true，遍历未完成时终止返回false
	 */
	private function visitNode( $path, $vistor, $exact_match=false, $all_req_paths=false){
		assert(count($path));
		$pos = &$this->arr;
		$next = &$pos;
		foreach ($path as $i){
			if(is_null($next)) {
			    return !$all_req_paths;
			}
			if(!array_key_exists($i, $next)){
			    if($exact_match){ 
			        return false;
			    }
			    if($i == self::$end){ 
			        return false;
			    }
			    //不要求完全匹配, 尝试匹配通配符
			    if(!array_key_exists(self::$wildcard, $next)){
			        return false;
			    }
			    $pos = &$next[self::$wildcard];
			}else{
			    $pos = &$next[$i];
			}
			if(!$vistor($i,$pos)){
				return false;
			}
			if(array_key_exists('next',$pos)){
				$next = &$pos['next'];
			}else{
				$nul = null;
				$next = &$nul; //$next = null 会导致引用的对象被赋值，而不是next被赋值
			}
		}
		return true;
	}
	/**
	 * 树打平输出成数组
	 * @return array
	 */
	public  function export(){
	    $res=array();
	    self::treeToArray($this->arr, $res);
	    return $res;
	}
	/**
	 * 
	 * @param array $tree
	 * @param array $res
	 * @return void
	 */
	static private  function treeToArray($tree, &$res){
	    foreach ($tree as $name=>$node){
	        if($node === null){
	            continue;
	        }
	        if( isset($node['value']) ){
	            $res[] = array(array($name), $node['value']);
	        }
	        if( isset($node['next']) ){
	            $tmp = array();
	            self::treeToArray($node['next'], $tmp);
	            foreach ($tmp as  $v){
	                array_unshift($v[0], $name);
	                $res[] = array($v[0], $v[1]);
	            }
	        }
	    }
	}
	private  $arr=array();
	static public $end="\n";
	static public $wildcard='*';
}
