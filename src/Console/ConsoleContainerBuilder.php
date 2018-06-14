<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/6/14
 * Time: 下午2:10
 */

namespace PhpBoot\Console;


use DI\FactoryInterface;
use Doctrine\Common\Cache\Cache;
use PhpBoot\Annotation\ContainerBuilder;
use DI\InvokerInterface as DIInvokerInterface;
use PhpBoot\Console\Annotations\ClassAnnotationHandler;
use PhpBoot\Console\Annotations\CommandAnnotationHandler;
use PhpBoot\Console\Annotations\CommandNameAnnotationHandler;
use PhpBoot\Console\Annotations\ParamAnnotationHandler;
use PhpBoot\Console\Annotations\ValidateAnnotationHandler;

class ConsoleContainerBuilder extends ContainerBuilder
{
    static $DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [CommandNameAnnotationHandler::class, "class.children[?name=='command']"],
        [CommandAnnotationHandler::class, "methods.*.children[?name=='command'][]"],
        [ParamAnnotationHandler::class, "methods.*.children[?name=='param'][]"],
        [ValidateAnnotationHandler::class, "methods.*.children[].children[?name=='v'][]"],
    ];
    /**
     * @var FactoryInterface
     */
    private $factory;
    /**
     * @var DIInvokerInterface
     */
    private $diInvoker;

    public function __construct(FactoryInterface $factory,
                                DIInvokerInterface $diInvoker,
                                Cache $cache,
                                array $annotations = null
    )
    {
        if($annotations){
            parent::__construct($annotations, $cache);
        }else{
            parent::__construct(self::$DEFAULT_ANNOTATIONS, $cache);
        }
        $this->factory = $factory;
        $this->diInvoker = $diInvoker;
    }

    /**
     * @param string $className
     * @return ConsoleContainer
     */
    protected function createContainer($className)
    {
        return $this->factory->make(ConsoleContainer::class, ['className'=>$className]);
    }

    protected function handleAnnotation($handlerName, $container, $ann)
    {
        $handler = $this->factory->make($handlerName);
        return $this->diInvoker->call($handler, [$container, $ann]);
    }

    protected function postCreateContainer($object)
    {
        parent::postCreateContainer($object);
        /**@var ConsoleContainer $object*/
        $object->postCreate();
    }
}