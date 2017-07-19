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
    public function __construct(ObjectDefinitionContext $context, self $parent=null){
        $this->context = $context;
        $this->parent = $parent;
    }
    /**
     * @var ObjectDefinitionContext
     */
    protected $context;
    /**
     * @var self
     */
    protected $parent;

    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $className = $this->context->definition->getClassName();
        if(!$this->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of $className should be used with parent route");
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