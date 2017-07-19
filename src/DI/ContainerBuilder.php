<?php
namespace PhpBoot\DI;

use DI\Container;

class ContainerBuilder extends \DI\ContainerBuilder
{
    /**
     * Build and return a container.
     *
     * @return Container
     */
    public function build()
    {
        $this->addDefinitions(new AnnotationReader());
        $this->useAutowiring(false);
        $this->useAnnotations(false);
        return parent::build();
    }
}