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

class VarAnnotationHandler implements AnnotationHandler
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
        //
        $params = new AnnotationParams($ann->description, 2);

        count($params)>0 or fail(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of $className::$target require at least one param, 0 given"));

        $this->context->vars[$target] = TypeHint::normalize($params[0], $className);
    }
}