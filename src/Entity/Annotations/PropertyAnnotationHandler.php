<?php
namespace PhpBoot\Entity\Annotations;

use PhpBoot\Metas\PropertyMeta;

class PropertyAnnotationHandler extends EntityAnnotationHandler
{

    public function handle($block)
    {
        $meta = $this->container->getProperty($block->name);
        if(!$meta){
            $meta = new PropertyMeta($block->name);
            $this->container->setProperty($block->name, $meta);
        }
        $meta->description = $block->description;
        $meta->summary = $block->summary;
    }
}