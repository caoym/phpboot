<?php

namespace PhpBoot\Annotation;


interface AnnotationHandler{

    const TYPE_CLASS = 'class';
    const TYPE_METHOD = 'method';
    const TYPE_PROPERTY = 'property';

    /**
     * @param string $type TYPE_CLASS/TYPE_METHOD/TYPE_PROPERTY
     * @param string $target the name of the class or method or property
     * @param string $name tag name
     * @param string $value
     * @return void
     */
    public function handle($type, $target, $name, $value);


}
