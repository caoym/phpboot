<?php
namespace PhpBoot\ORM\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\ORM\ModelContainer;
use PhpBoot\Utils\AnnotationParams;

class TableAnnotationHandler
{
    /**
     * @param ModelContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ModelContainer $container, $ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        $table = $params->getParam(0) or \PhpBoot\abort(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()} require at least one param, 0 given"));

        $container->setTable($table);
    }
}