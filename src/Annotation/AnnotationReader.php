<?php

namespace PhpBoot\Annotation;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\Cache;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\ClassModifiedChecker;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;

/**
 * AnnotationEnabledTest
 */
class AnnotationEnabledTest
{
    /**
     * testMethod
     */
    public function testMethod()
    {

    }
}

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

    static public function assertAnnotationEnabled()
    {
        $rfl = new \ReflectionClass(AnnotationEnabledTest::class);
        !empty($rfl->getDocComment()) or \PhpBoot\abort('Annotation dose not work! If opcache is enable, please set opcache.save_comments=1 and opcache.load_comments=1');
    }
    /**
     * load from class with local cache
     * TODO 增加 filter 能力
     * @param string $className
     * @param Cache $localCache
     * @return object
     */
    static public function read($className, Cache $localCache = null)
    {
        self::assertAnnotationEnabled();
        $rfl = new \ReflectionClass($className) or \PhpBoot\abort("load class $className failed");
        $fileName = $rfl->getFileName();
        $key = str_replace('\\','.',self::class).md5($fileName.$className);
        $oldData = null;
        $cache = new CheckableCache($localCache?:new ApcCache());
        $res = $cache->get('ann:'.$key, null, $oldData, false);
        if($res === null){
            try{
                $meta = self::readWithoutCache($className);
                $cache->set($key, $meta, 0, $fileName?new ClassModifiedChecker($className):null);
                return $meta;
            }catch (\Exception $e){
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
            if ($i->isStatic()) {
                continue;
            }
            $block = self::readAnnotationBlock($i->getDocComment());
            $block->name = $i->getName();
            $reader->properties[$i->getName()]=$block;
        }
        while ($rfl = $rfl->getParentClass()) {
            foreach ($rfl->getProperties(\ReflectionProperty::IS_PRIVATE) as $i) {
                if ($i->isStatic()) {
                    continue;
                }
                $block = self::readAnnotationBlock($i->getDocComment());
                $block->name = $i->getName();
                $reader->properties[$i->getName()]=$block;
            }
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