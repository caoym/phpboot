<?php

namespace PhpBoot\Annotation;

use Doctrine\Common\Cache\Cache;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\ClassModifiedChecker;
use PhpBoot\Utils\Logger;

abstract class ContainerBuilder
{
    /**
     * ContainerBuilder constructor.
     * @param array $annotations 需加载的注释和顺序
     * @param Cache $cache
     * 语法 http://jmespath.org/tutorial.html
     *
     *  [
     *      [PropertyAnnotationHandler::class,   'property'],
     *      ...
     *  ];
     *
     */
    public function __construct(array $annotations, Cache $cache)
    {
        $this->annotations = $annotations;
        $this->cache = $cache;
    }

    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return object
     */
    public function build($className)
    {
        //TODO【重要】 使用全局的缓存版本号, 而不是针对每个文件判断缓存过期与否
        $rfl = new \ReflectionClass($className) or \PhpBoot\abort("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = str_replace('\\','.',get_class($this)).md5(serialize($this->annotations).$fileName.$className);
        $cache = new CheckableCache($this->cache);
        $res = $cache->get($key, $this);
        if($res === $this){
            try{
                $meta = $this->buildWithoutCache($className);
                $cache->set($key, $meta, 0, $fileName?new ClassModifiedChecker($className):null);
                return $meta;
            }catch (\Exception $e){
                Logger::warning(__METHOD__.' failed with '.$e->getMessage());
                $cache->set($key, $e->getMessage(), 0, $fileName?new ClassModifiedChecker($className):null);
                throw $e;
            }
        }elseif(is_string($res)){
            \PhpBoot\abort($res);
        }else{
            return $res;
        }
    }

    /**
     * @param string $className
     * @return object
     */
    abstract protected function createContainer($className);


    protected function handleAnnotation($handlerName, $container, $ann){
        $handler = new $handlerName();
        return $handler($container, $ann);
    }
    /**
     * @param $className
     * @return object
     */
    public function buildWithoutCache($className)
    {
        $container = $this->createContainer($className);
        $anns = AnnotationReader::read($className, $this->cache);
        foreach ($this->annotations as $i){
            list($class, $target) = $i;

            $found = \JmesPath\search($target, $anns);
            if(is_array($found)){
                foreach ($found as $f){
                    $this->handleAnnotation($class, $container,$f); //TODO 支持
                }
            }else{
                $this->handleAnnotation($class, $container, $found);
            }
        }
        return $container;
    }

    /**
     * @var array
     */
    private $annotations=[];
    /**
     * @var Cache
     */
    private $cache;
}