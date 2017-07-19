<?php

namespace PhpBoot\DI;

use DI\Definition\EntryReference;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\ObjectDefinition\MethodInjection;
use PhpBoot\Exceptions\AnnotationSyntaxException;

class AnnotationReader implements DefinitionSource
{

    /**
     * {@inheritdoc}
     * @throws AnnotationSyntaxException
     * @throws \InvalidArgumentException The class doesn't exist
     */
    public function getDefinition($name)
    {
        if (!class_exists($name) && !interface_exists($name)) {
            return null;
        }

        $loader = new DIMetaLoader();
        $context = $loader->loadFromClass($name);
        /**@var $context ObjectDefinitionContext */
        $class = new \ReflectionClass($name);
        $definition = $context->definition;

        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $definition->setConstructorInjection(
                MethodInjection::constructor($this->getParametersDefinition($constructor))
            );
        }

        return $definition;
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition(\ReflectionFunctionAbstract $constructor)
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            // Skip optional parameters
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $parameters[$index] = new EntryReference($parameterClass->getName());
            }
        }

        return $parameters;
    }
}