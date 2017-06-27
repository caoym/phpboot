<?php

namespace PhpBoot\Annotation;


use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Lock\LocalAutoLock;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\ObjectAccess;
use Symfony\Component\Cache\Simple\ApcuCache;

abstract class MetaLoader
{
    /**
     * MetaLoader constructor.
     * @param array $annotations 需加载的注释和顺序
     *
     *  [
     *      [PropertyMeta::class,   '$.property'],
     *      ...
     *  ];
     *
     */
    public function __construct(array $annotations)
    {
        $acc = new ObjectAccess($this->annotations);
        foreach ($annotations as $i){
            $acc->set($i[1], $i[0]);
        };
        $this->cache = new CheckableCache(new ApcuCache());
    }

    /**
     * load from class with local cache
     * @param string $className
     * @return object|false
     */
    public function loadFromClass($className)
    {
        $rfl = new \ReflectionClass($className) or fail("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = get_class($this).md5($fileName);
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
                        $this->cache->set($key, false, 0, new FileExpiredChecker($fileName));
                        return false;
                    }
                },
                function () use($oldData){
                    return $oldData;
                });
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
        $rfl = new \ReflectionClass($className);
        $builder = $this->createBuilder($className);
        //class annotations;
        $this->handleAnnotation($builder, AnnotationHandler::TYPE_CLASS, $className, $rfl->getDocComment());
        //method annotations
        foreach ($rfl->getMethods() as $i){
            $this->handleAnnotation($builder, AnnotationHandler::TYPE_METHOD, $i->getName(), $i->getDocComment());
        }
        //property annotations
        foreach ($rfl->getProperties() as $i){
            $this->handleAnnotation($builder, AnnotationHandler::TYPE_PROPERTY, $i->getName(), $i->getDocComment());
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