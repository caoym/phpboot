<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/6/14
 * Time: 下午7:31
 */

namespace PhpBoot\Tests;

use PhpBoot\Console;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCommand
 * @package PhpBoot\Tests
 *
 * @command test
 */
class TestCommand
{
    /**
     * run test
     *
     * the run test
     * @command run
     *
     * @param int $arg0 arg 0
     * @param string $arg1 arg 1
     * @param string[] $arg2 arg 2
     */
    public function runTest($arg0, $arg1, $arg2){
        print_r([$arg0, $arg1, $arg2]);
        return 0;
    }
}

class ConsoleTest extends TestCase
{
    public function testCommand()
    {
        $console = $this->app->make(Console::class);
        /**@var Console $console*/
        $console->loadCommandsFromClass(TestCommand::class);
        $console->setAutoExit(false);
        $output = new BufferedOutput();
        $console->run(new StringInput("test.run 11 22 33"), $output);

        self::assertEquals($output->fetch(), print_r(["11", "22", ["33"]], true));
    }
}