# 命令行

使用PHP开发非http服务时，如定时任务等，常需要通过命令行模式启动php脚本，PhpBoot的CLI支持可以让你快速完成这方面工作。


## 1. 实现命令行

```php
/**
 * @command test    //可选 @command指定命令的前缀
 */
namespace App\Commands

class TestCommand
{
    /**
     * run test
     *
     * the run test
     * @command run    // 命令唯一标识
     *
     * @param int $arg0 arg 0
     * @param string $arg1 arg 1
     * @param string[] $arg2 arg 2
     */
    public function runTest($arg0, $arg1, $arg2){
        print_r([$arg0, $arg1, $arg2]);
        return 0; // 返回进程的exit code
    }
}

```

## 2. 编写入口文件 cli.php

```php

use \PhpBoot\Application;
use \PhpBoot\Console;

ini_set('date.timezone','Asia/Shanghai');
require __DIR__.'/../vendor/autoload.php';

// 加载配置
$app = Application::createByDefault(__DIR__ . '/../config/config.php');
// 加载命令行
$console = Console::create($app);
$console->loadCommandsFromPath(__DIR__.'/../App/Commands', 'App\\Commands');
// 执行命令行
$console->run();

```

## 3. 执行命令

> 执行 php ./cli.php 
```shell

$ php ./cli.php 
Console Tool

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help     Displays help for a command
  list     Lists commands
  my.test   : run test


```

> 执行 php ./cli.php my.test 1 2 a b 

```shell

$ php ./cli.php my.test 1 2 a b 
array(3) {
  [0]=>
  int(1)
  [1]=>
  string(1) "2"
  [2]=>
  array(2) {
    [0]=>
    string(1) "a"
    [1]=>
    string(1) "b"
  }
}
```