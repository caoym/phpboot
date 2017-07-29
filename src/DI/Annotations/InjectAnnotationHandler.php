<?php
namespace PhpBoot\DI\Annotations;

use DI\Definition\EntryReference;
use DI\Definition\ObjectDefinition\PropertyInjection;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\DI\ObjectDefinitionContext;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;

class InjectAnnotationHandler
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
        // @inject a.b.c
        $params = new AnnotationParams($ann->description, 3);
        if(count($params) == 0){
            //查找@var 定义的变量
            if(!isset($context->vars[$target])){
                Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of $className should be used with a param or @var");
            }
            $entryName = $context->vars[$target];
        }else{
            $entryName = $params[0];
        }

        $context->definition->addPropertyInjection(
            new PropertyInjection($target, new EntryReference($entryName), $className)
        );
    }
}