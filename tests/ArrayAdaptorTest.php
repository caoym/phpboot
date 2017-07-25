<?php

namespace PhpBoot\Tests;

use PhpBoot\Utils\ArrayAdaptor;

class AccessByProperty{
    public $key;
}

class AccessByMethod1{
    public function set($name, $val)
    {
        $this->$name = $val;
    }
    public function get($name){
        return $this->$name;
    }
    public function has($name){
        return isset($this->$name);
    }
    public function remove($name){
        unset($this->$name);
    }
}

class AccessByMethod2{
    public function setKey($val)
    {
        $this->key = $val;
    }
    public function getKey(){
        return $this->key;
    }
    public function hasKey(){
        return isset($this->key);
    }
    public function removeKey()
    {
        unset($this->key);
    }
    private $key;
}



class ArrayAdaptorTest extends TestCase
{
    public function testArray()
    {
        $src = [];
        $adt = new ArrayAdaptor($src);
        self::assertFalse(isset($adt[0]));

        $adt[0]=1;
        self::assertTrue(isset($adt[0]));
        self::assertEquals($adt[0], $src[0]);
        self::assertEquals($adt[0], 1);

        unset($adt[0]);
        self::assertFalse(isset($adt[0]));
        self::assertFalse(isset($arr[0]));
    }

    public function testAccessByProperty()
    {
        $src = new AccessByProperty();
        $adt = new ArrayAdaptor($src);

        $adt['key']=1;
        self::assertEquals($adt['key'], $src->key);
        self::assertEquals($adt['key'], 1);
    }

    public function testAccessByMethod1()
    {
        $src = new AccessByMethod1();
        $adt = new ArrayAdaptor($src);
        self::assertFalse(isset($adt['key']));

        $adt['key']=1;
        self::assertTrue(isset($adt['key']));
        self::assertEquals($adt['key'], $src->get('key'));
        self::assertEquals($adt['key'], 1);

        unset($adt['key']);
        self::assertFalse(isset($adt['key']));
        self::assertFalse($src->has('key'));
    }

    public function testAccessByMethod2()
    {
        $src = new AccessByMethod2();
        $adt = new ArrayAdaptor($src);
        self::assertFalse(isset($adt['key']));

        $adt['key']=1;
        self::assertTrue(isset($adt['key']));
        self::assertEquals($adt['key'], $src->getKey());
        self::assertEquals($adt['key'], 1);

        unset($adt['key']);
        self::assertFalse(isset($adt['key']));
        self::assertFalse($src->hasKey());
    }
}