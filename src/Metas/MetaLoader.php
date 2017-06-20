<?php

namespace PhpBoot\Metas;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Lock\LocalAutoLock;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\ObjectAccess;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use Symfony\Component\Cache\Simple\ApcuCache;

/**
 * Mate loader
 */
class MetaLoader
{
    const DEFAULT_ANNOTATIONS=[
        [RouteGroupMeta::class, '$.class'],
        [RouteMeta::class,  '$.method.route'],
        [PropertyMeta::class,   '$.property'],

    ];
    /**
     * MetaLoader constructor.
     * @param array $annotations 需加载的注释和顺序
     */
    public function __construct(array $annotations = self::DEFAULT_ANNOTATIONS)
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
     * @return RouteGroupMeta|false
     */
    public function loadFromClass($className)
    {
        $rfl = new \ReflectionClass($className) or fail("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = 'MetaLoader:'.md5($fileName);
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
                        Logger::warning('loadFromClass failed with '.$e->getMessage());
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
    public function loadFromClassWithoutCache($className)
    {
        $rfl = new \ReflectionClass($className);
        $doc = $this->createDocBlockFactory();

        if($rfl->getDocComment()) {
            $docBlock = $doc->create($rfl->getDocComment());
            $tags = $docBlock->getTags();
            //class annotations;
            foreach ($tags as $tag) {
                $h = $this->getAnnotationHandler($tag->getName(), '$.class');
                if($h){
                    $doc = $tag->getDescription();
                    $doc->handle
                }
                $visitor(self::TYPE_CLASS, $className, $tag->getName(), strval($tag->getDescription()));
            }
        }
    }

    private function createDocBlockFactory(){
        $fqsenResolver = new FqsenResolver();
        $tagFactory = new StandardTagFactory($fqsenResolver,[]);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService(new TypeResolver($fqsenResolver));
        $docBlockFactory = new DocBlockFactory($descriptionFactory, $tagFactory);
        return $docBlockFactory;
    }

    /**
     * @param $name
     * @param $target
     */
    public function getAnnotationHandler($name, $target)
    {

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