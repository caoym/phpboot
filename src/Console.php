<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/6/14
 * Time: 下午6:11
 */

namespace PhpBoot;

use DI\FactoryInterface;
use DI\InvokerInterface;
use PhpBoot\Console\ConsoleContainer;
use PhpBoot\Console\ConsoleContainerBuilder;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends \Symfony\Component\Console\Application
{
    use EnableDIAnnotations;

    /**
     * @inject
     * @var ConsoleContainerBuilder
     */
    protected $consoleContainerBuilder;

    /**
     * @inject
     * @var InvokerInterface
     */
    private $diInvoker;

    /**
     * @param FactoryInterface $factory
     * @return Console
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    static public function create(FactoryInterface $factory)
    {
        return $factory->make(self::class);
    }
    /**
     * @param $className
     * @throws \Exception
     */
    public function loadCommandsFromClass($className)
    {
        $console = null;
        $container = null;

        $container = $this->consoleContainerBuilder->build($className);
        /**@var ConsoleContainer $container*/
        foreach ($container->getCommands() as $name => $command) {
            $command->setCode(function (InputInterface $input, OutputInterface $output)use ($container, $command){
                return $this->diInvoker->call([$command, 'invoke'], ['container'=>$container, 'input'=>$input, 'output'=>$output]);
            });
            $this->add($command);
        }
    }

    /**
     * @param $fromPath
     * @param string $namespace
     * @throws \Exception
     */
    public function loadCommandsFromPath($fromPath, $namespace = '')
    {
        $dir = @dir($fromPath) or abort("dir $fromPath not exist");

        $getEach = function () use ($dir) {
            $name = $dir->read();
            if (!$name) {
                return $name;
            }
            return $name;
        };

        while (!!($entry = $getEach())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $path = $fromPath . '/' . str_replace('\\', '/', $entry);
            if (is_file($path) && substr_compare($entry, '.php', strlen($entry) - 4, 4, true) == 0) {
                $class_name = $namespace . '\\' . substr($entry, 0, strlen($entry) - 4);
                $this->loadCommandsFromClass($class_name);
            } else {
                //\Log::debug($path.' ignored');
            }
        }
    }

}