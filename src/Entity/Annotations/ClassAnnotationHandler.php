<?php

namespace PhpBoot\Entity\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Metas\PropertyMeta;

class ClassAnnotationHandler
{
    /**
     * @param EntityContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function __invoke(EntityContainer $container, $ann)
    {
        $ref = new \ReflectionClass($container->getClassName());
        $container->getClassName();
        $properties = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
        $default = $ref->getDefaultProperties();
        $container->setFileName($ref->getFileName());

        $container->setDescription($ann->description);
        $container->setSummary($ann->summary);

        foreach ($properties as $i){
            $isOption = array_key_exists($i->getName(), $default) && $default[$i->getName()] !==null;
            $container->setProperty($i->getName(), new PropertyMeta(
                $i->getName(),
                null,
                $isOption,
                $isOption?$default[$i->getName()]:null
                ));
        }
    }
}