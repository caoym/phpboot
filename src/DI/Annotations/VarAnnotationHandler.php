<?php
namespace PhpBoot\DI\Annotations;

use DI\Definition\EntryReference;
use DI\Definition\ObjectDefinition\PropertyInjection;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\DI\ObjectDefinitionContext;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class VarAnnotationHandler
{

    /**
     * @param ObjectDefinitionContext $context
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function __invoke(ObjectDefinitionContext $context, $ann)
    {
        $className = $context->definition->getClassName();
        if(!$ann->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of $className should be used with parent");
        }
        $target = $ann->parent->name;
        //
        $params = new AnnotationParams($ann->description, 2);

        count($params)>0 or \PhpBoot\abort(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of $className::$target require at least one param, 0 given"));

        $context->vars[$target] = TypeHint::normalize($params[0], $className);
    }
}