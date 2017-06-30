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
        $ref = new \ReflectionClass($this->builder->getClassName());
        $this->builder->getClassName();
        $properties = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
        $default = $ref->getDefaultProperties();
        $this->builder->setFileName($ref->getFileName());

        $this->builder->setDescription($ann->description);
        $this->builder->setSummary($ann->summary);

        foreach ($properties as $i){
            $isOption = array_key_exists($i->getName(), $default) && $default[$i->getName()] !==null;
            $this->builder->setProperty($i->getName(), new PropertyMeta(
                $i->getName(),
                null,
                $isOption,
                $isOption?$default[$i->getName()]:null
                ));
        }
    }
}