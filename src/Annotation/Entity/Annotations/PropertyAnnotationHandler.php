<?php
namespace PhpBoot\Annotation\Entity\Annotations;

use PhpBoot\Annotation\Entity\EntityAnnotationHandler;
use PhpBoot\Metas\PropertyMeta;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class PropertyAnnotationHandler extends EntityAnnotationHandler
{

    public function handle($block)
    {
        $meta = new PropertyMeta($block->name);
        $meta->description = $block->description;
        $meta->summary = $block->summary;
        $this->builder->setProperty($block->name, $meta);
    }
}