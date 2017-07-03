<?php

namespace PhpBoot\Annotation;
use Doctrine\Common\Cache\ApcuCache;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Lock\LocalAutoLock;
use PhpBoot\Utils\Logger;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;


class AnnotationTagsOutput implements Formatter
{
    /**
     * Formats a tag into a string representation according to a specific format, such as Markdown.
     *
     * @param Tag $tag
     *
     * @return string
     */
    public function format(Tag $tag)
    {
        $this->tags[] = $tag;
        return strval($tag);
    }
    public $tags = [];
}
class AnnotationReader implements \ArrayAccess
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
     * @return object
     */
    static public function read($className)
    {
        $rfl = new \ReflectionClass($className) or fail("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = str_replace('\\','.',self::class).md5($fileName.$className);
        $oldData = null;
        $cache = new CheckableCache(new ApcuCache());
        $res = $cache->get('lock.'.$key, null, $oldData, false);
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
                        $cache->set($key, $e->getMessage(), 0, new FileExpiredChecker($fileName));
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
     * @param $className
     * @return self
     */
    static function readWithoutCache($className)
    {
        $reader = new self();

        $rfl = new \ReflectionClass($className);
        $reader->class = self::readAnnotationBlock($rfl->getDocComment());
        $reader->class->name = $className;

        //method annotations
        foreach ($rfl->getMethods() as $i){
            $block = self::readAnnotationBlock($i->getDocComment());
            $block->name = $i->getName();
            $reader->methods[$i->getName()]=$block;
        }
        //property annotations
        foreach ($rfl->getProperties() as $i){
            $block = self::readAnnotationBlock($i->getDocComment());
            $block->name = $i->getName();
            $reader->properties[$i->getName()]=$block;
        }
        return $reader;
    }

    static private function readAnnotationBlock($doc)
    {
        $annBlock = new AnnotationBlock();
        if(empty($doc)){
            return $annBlock;
        }
        $docFactory = AnnotationReader::createDocBlockFactory();
        $docBlock = $docFactory->create($doc);
        $annBlock->summary = $docBlock->getSummary();
        $annBlock->description = strval($docBlock->getDescription());
        $annBlock->children = [];
        $tags = $docBlock->getTags();
        foreach ($tags as $tag) {
            $annTag = new AnnotationTag();
            $desc = $tag->getDescription();
            $annTag->parent = $annBlock;
            $annTag->description = strval($desc);
            $annTag->name = $tag->getName();
            $annTag->children=[];
            if($desc){
                $output = new AnnotationTagsOutput();
                $desc->render($output);
                foreach ($output->tags as $child) {
                    $childTag = new AnnotationTag();
                    $childTag->parent = $annTag;
                    $childTag->name = $child->getName();
                    $childTag->description = strval($child->getDescription());
                    $annTag->children[] = $childTag;
                }
            }
            $annBlock->children[] = $annTag;
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

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}