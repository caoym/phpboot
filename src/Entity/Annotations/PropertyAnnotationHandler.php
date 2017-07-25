<?php
namespace PhpBoot\Entity\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Metas\PropertyMeta;

class PropertyAnnotationHandler
{
    /**
     * @param EntityContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function __invoke(EntityContainer $container, $ann)
    {
        $meta = $container->getProperty($ann->name);
        if(!$meta){
            $meta = new PropertyMeta($ann->name);
            $container->setProperty($ann->name, $meta);
        }
        $meta->description = $ann->description;
        $meta->summary = $ann->summary;
    }
}