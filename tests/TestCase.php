<?php

namespace PhpBoot\Tests;


use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use PhpBoot\Application;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->app = Application::createByDefault([
            Cache::class => \DI\object(FilesystemCache::class)
                ->constructorParameter('directory', sys_get_temp_dir())
        ]);
    }
    protected static function assertException(callable  $fun, $expectedClass = null, $expectedMessage = null){
        $throw = false;
        try{
            $fun();
        }catch (\Exception $e){
            $throw = true;
            if($expectedClass){
                self::assertInstanceOf($expectedClass, $e);
            }
            if($expectedMessage !== null){
                self::assertEquals($expectedMessage, $e->getMessage());
            }
        }
        self::assertTrue($throw);
    }
    /**
     * @var Application
     */
    protected $app;
}