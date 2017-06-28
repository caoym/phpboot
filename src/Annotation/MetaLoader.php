<?php

namespace PhpBoot\Annotation;


use Doctrine\Common\Cache\ApcuCache;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Lock\LocalAutoLock;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\ObjectAccess;

abstract class MetaLoader
{
    /**
     * MetaLoader constructor.
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
        $this->cache = new CheckableCache(new ApcuCache());
    }

    /**
     * load from class with local cache
     * @param string $className
     * @return object
     */
    public function loadFromClass($className)
    {
        //TODO【重要】 使用全局的缓存版本号, 而不是针对每个文件判断缓存过期与否
        $rfl = new \ReflectionClass($className) or fail("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = get_class($this).md5(serialize($this->annotations).$fileName);
        $oldData = null;
        $res = $this->cache->get($key, null, $oldData, false);
        if($res === null){
            return LocalAutoLock::lock(
                $key,
                60,
                function () use($key, $className, $fileName){
                    try{
                        $meta = $this->loadFromClassWithoutCache($className);
                        $this->cache->set($key, $meta, 0, new FileExpiredChecker($fileName));
                        return $meta;
                    }catch (\Exception $e){
                        Logger::warning(__METHOD__.' failed with '.$e->getMessage());
                        $this->cache->set($key, $e->getMessage(), 0, new FileExpiredChecker($fileName));
                        throw $e;
                    }
                },
                function () use($oldData){
                    return $oldData;
                });
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
    protected abstract function createBuilder($className);

    /**
     * @param $className
     * @return object
     */
    public function loadFromClassWithoutCache($className)
    {
        $builder = $this->createBuilder($className);
        $anns = AnnotationReader::read($className);
        foreach ($this->annotations as $i){
            list($class, $target) = $i;

            $handler = new $class($builder);
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
        return $builder;
    }

    /**
     * @param mixed $userData
     * @param string $type
     * @param AnnotationHandler|null $parent
     * @return AnnotationHandler|null
     */
    public function getAnnotationHandler($userData, $type, $parent=null)
    {
        $acc = new ObjectAccess($this->annotations);
        $class = $acc->get($type);
        if($class){
            class_exists($class) or fail("Annotation Handler class $class not exist");
            return new $class($userData, $parent);
        }
        return null;
    }
    private function handleAnnotation($builder, $type, $target, $doc)
    {
        $docFactory = AnnotationReader::createDocBlockFactory();
        $docBlock = $docFactory->create($doc);
        $h = $this->getAnnotationHandler($builder, '$.'.$type);

        $tags = $docBlock->getTags();
        //class annotations;
        foreach ($tags as $tag) {
            $h = $this->getAnnotationHandler($builder, '$.'.$type);
            if($h){
                $h->handle($type, $target, $tag->getName(), $docBlock->getDescription());

                $childBlock = $docFactory->create($docBlock->getDescription());
                $childTags = $childBlock->getTags();

                foreach ($childTags as $child) {
                    $childHandler = $this->getAnnotationHandler($builder, '$.'.$type.'.'.$tag->getName(), $h);
                    if($childHandler){
                        $childHandler->handle($type, $target, $child->getName(), $childBlock->getDescription());
                    }
                }
            }
        }
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