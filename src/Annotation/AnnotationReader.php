<?php

namespace PhpBoot\Annotation;


use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Lock\LocalAutoLock;
use PhpBoot\Utils\Logger;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use Symfony\Component\Cache\Simple\ApcuCache;

class AnnotationReader
{
    static public function createDocBlockFactory(){
        $fqsenResolver = new FqsenResolver();
        $tagFactory = new StandardTagFactory($fqsenResolver,[]);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService(new TypeResolver($fqsenResolver));
        $docBlockFactory = new DocBlockFactory($descriptionFactory, $tagFactory);
        return $docBlockFactory;
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return object|false
     */
    static public function read($className)
    {
        $rfl = new \ReflectionClass($className) or fail("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = self::class.md5($fileName);
        $oldData = null;
        $cache = new CheckableCache(new ApcuCache());
        $res = $cache->get($key, null, $oldData, false);
        if($res === null){
            return LocalAutoLock::lock(
                $key,
                60,
                function () use($key, $className, $fileName, $cache){
                    try{
                        $meta = self::readWithoutCache($className);
                        $cache->set($key, $meta, 0, new FileExpiredChecker($fileName));
                        return $meta;
                    }catch (\Exception $e){
                        Logger::warning(__METHOD__.' failed with '.$e->getMessage());
                        $cache->set($key, false, 0, new FileExpiredChecker($fileName));
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
     * @param $className
     * @return self
     */
    static function readWithoutCache($className)
    {
        $reader = new self();

        $rfl = new \ReflectionClass($className);
        $reader->class = self::readAnnotationBlock($rfl->getDocComment());

        //method annotations
        foreach ($rfl->getMethods() as $i){
            $block = self::readAnnotationBlock($i->getDocComment());
            $reader->methods[$i->getName()]=$block;
        }
        //property annotations
        foreach ($rfl->getProperties() as $i){
            $block = self::readAnnotationBlock($i->getDocComment());
            $reader->properties[$i->getName()]=$block;
        }
        return $reader;
    }

    static private function readAnnotationBlock($doc)
    {
        $annBlock = new AnnotationBlock();
        $docFactory = AnnotationReader::createDocBlockFactory();
        $docBlock = $docFactory->create($doc);

        $annBlock->summary = $docBlock->getSummary();
        $annBlock->description = $docBlock->getDescription();
        $annBlock->children = [];
        $tags = $docBlock->getTags();
        foreach ($tags as $tag) {
            $block = new AnnotationBlock();
            $block->description = $tag->getDescription();
            $block->name = $tag->getName();
            $block->children=[];
                $childBlock = $docFactory->create($block->description);
                $childTags = $childBlock->getTags();
                foreach ($childTags as $child) {
                    $childBlock = new AnnotationBlock();
                    $childBlock->name = $child->getName();
                    $childBlock->description = $child->getDescription();
                    $block->children[] = $childBlock;
                }
            $annBlock->children[] = $block;
        }
        return $annBlock;
    }

    /**
     * @var AnnotationBlock
     */
    public $class;
    /**
     * @var AnnotationBlock[]
     */

    public $methods=[];
    /**
     * @var AnnotationBlock[]
     */
    public $properties=[];

}