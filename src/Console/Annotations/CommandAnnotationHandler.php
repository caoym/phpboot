<?php

namespace PhpBoot\Console\Annotations;

use DI\InvokerInterface;
use FastRoute\RouteParser\Std;
use PhpBoot\Console\Command;
use PhpBoot\Console\ConsoleContainer;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\MixedTypeContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Metas\ParamMeta;
use PhpBoot\Utils\AnnotationParams;

class CommandAnnotationHandler
{
    public function __invoke(ConsoleContainer $container, $ann, EntityContainerBuilder $entityBuilder)
    {
        $params = new AnnotationParams($ann->description, 2);
        $target = $ann->parent->name;
        $name = $params->getParam(0, $target);

        //获取方法参数信息
        $rfl =  new \ReflectionClass($container->getClassName());
        $method = $rfl->getMethod($target);
        $methodParams = $method->getParameters();

        $command = new Command($target, $name);
        $command->setDescription($container->getSummary().' : '.$ann->parent->summary);
        $command->setHelp($ann->parent->description);

        //设置参数列表
        $paramsMeta = [];
        foreach ($methodParams as $param){
            $paramName = $param->getName();
            $source = "argv.$paramName";
            $paramClass = $param->getClass();
            if($paramClass){
                $paramClass = $paramClass->getName();
            }
            $entityContainer = ContainerFactory::create($entityBuilder, $paramClass);
            $meta = new ParamMeta($paramName,
                $source,
                $paramClass?:'mixed',
                $param->isOptional(),
                $param->isOptional()?$param->getDefaultValue():null,
                $param->isPassedByReference(),
                null,
                '',
                $entityContainer
            );
            $paramsMeta[] = $meta;
        }
        $command->setParamMetas($paramsMeta);
        $container->addCommand($target, $command);
    }
}