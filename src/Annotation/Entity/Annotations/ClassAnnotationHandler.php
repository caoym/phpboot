<?php

namespace PhpBoot\Annotation\Entity\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\Entity\EntityAnnotationHandler;
use PhpBoot\Metas\PropertyMeta;

class ClassAnnotationHandler extends EntityAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $ref = new \ReflectionClass($this->container->getClassName());
        $this->container->getClassName();
        $properties = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
        $default = $ref->getDefaultProperties();
        $this->container->setFileName($ref->getFileName());

        $this->container->setDescription($ann->description);
        $this->container->setSummary($ann->summary);

        foreach ($properties as $i){
            $isOption = array_key_exists($i->getName(), $default) && $default[$i->getName()] !==null;
            $this->container->setProperty($i->getName(), new PropertyMeta(
                $i->getName(),
                null,
                $isOption,
                $isOption?$default[$i->getName()]:null
                ));
        }
    }
}