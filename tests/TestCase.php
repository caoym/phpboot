<?php

namespace PhpBoot\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use PhpBoot\Application;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        static $cache = null;
        if(!$cache){
            $cache = new ArrayCache();
        }
        $this->app = Application::createByDefault([
            Cache::class => $cache
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