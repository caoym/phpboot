<?php

namespace PhpBoot\Annotation;

use Doctrine\Common\Cache\ApcCache;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Utils\Logger;

abstract class ContainerBuilder
{
    /**
     * ContainerBuilder constructor.
     * @param array $annotations 需加载的注释和顺序
     *
     * 语法 http://jmespath.org/tutorial.html
     *
     *  [
     *      [PropertyAnnotationHandler::class,   'property'],
     *      ...
     *  ];
     *
     */
    public function __construct(array $annotations)
    {
        $this->annotations = $annotations;
        $this->cache = new CheckableCache(new ApcCache());
    }

    /**
     * load from class with local cache
     * @param string $className
     * @return object
     */
    public function build($className)
    {
        //TODO【重要】 使用全局的缓存版本号, 而不是针对每个文件判断缓存过期与否
        $rfl = new \ReflectionClass($className) or fail("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = str_replace('\\','.',get_class($this)).md5(serialize($this->annotations).$fileName.$className);
        $oldData = null;
        $res = $this->cache->get($key, null, $oldData, false);
        if($res === null){
            try{
                $meta = $this->buildWithoutCache($className);
                $this->cache->set($key, $meta, 0, $fileName?new FileExpiredChecker($fileName):null);
                return $meta;
            }catch (\Exception $e){
                Logger::warning(__METHOD__.' failed with '.$e->getMessage());
                $this->cache->set($key, $e->getMessage(), 0, $fileName?new FileExpiredChecker($fileName):null);
                throw $e;
            }
        }elseif(is_string($res)){
            fail($res);
        }else{
            return $res;
        }
    }

    /**
     * @param string $className
     * @return object
     */
    protected abstract function createContainer($className);

    /**
     * @param $className
     * @return object
     */
    public function buildWithoutCache($className)
    {
        $container = $this->createContainer($className);
        $anns = AnnotationReader::read($className);
        foreach ($this->annotations as $i){
            list($class, $target) = $i;

            $handler = new $class($container);
            /** @var $handler AnnotationHandler*/
            $found = \JmesPath\search($target, $anns);
            if(is_array($found)){
                foreach ($found as $f){
                    $handler->handle($f);
                }
            }else{
                $handler->handle($found);
            }
        }
        return $container;
    }
    /**
     * @var array
     */
    private $annotations=[];
    /**
     * @var CheckableCache
     */
    private $cache;
}