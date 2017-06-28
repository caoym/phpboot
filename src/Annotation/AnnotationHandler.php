<?php

namespace PhpBoot\Annotation;


interface AnnotationHandler{

    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann);

}
