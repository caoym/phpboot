<?php
namespace PhpBoot\DI\Annotations;

use DI\Definition\EntryReference;
use DI\Definition\ObjectDefinition\PropertyInjection;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\DI\ObjectDefinitionContext;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;

class InjectAnnotationHandler implements AnnotationHandler
{
    public function __construct(ObjectDefinitionContext $context){
        $this->context = $context;
    }
    /**
     * @var ObjectDefinitionContext
     */
    protected $context;

    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $className = $this->context->definition->getClassName();
        if(!$ann->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of $className should be used with parent");
        }
        $target = $ann->parent->name;
        // @inject a.b.c
        $params = new AnnotationParams($ann->description, 3);
        if(count($params) == 0){
            //查找@var 定义的变量
            if(!isset($this->context->vars[$target])){
                Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of $className should be used with a param or @var");
            }
            $entryName = $this->context->vars[$target];
        }else{
            $entryName = $params[0];
        }

        $this->context->definition->addPropertyInjection(
            new PropertyInjection($target, new EntryReference($entryName), $className)
        );
    }
}