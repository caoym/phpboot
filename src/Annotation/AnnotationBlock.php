<?php

namespace PhpBoot\Annotation;


class AnnotationBlock
{
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string
     */
    public $summary = '';
    /**
     * @var string
     */
    public $description='';
    /**
     * @var AnnotationBlock[]
     */
    public $children=[];
}