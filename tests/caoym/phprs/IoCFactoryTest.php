<?php
use caoym\util\IoCFactory;
use caoym\util\Logger;

require_once dirname(dirname(__FILE__)).'/Autoload.php';

Logger::$writer = Logger::$to_echo;

class ClassA{
    /**
     * @property
     */
    public $classB;
}

class ClassB{
    /**
     * @property
     */
    public $classA;
}
/**
 * IocFactory test case.
 */
class IocFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCyclicDependenciesWithoutSingleton(){
        $this->setExpectedException('Exception');
        $factory = new IoCFactory(array(
            'ClassA' => array(
                'properties' => array(
                    'classB' => '@ClassB',
                ),
            ),
            'ClassB' => array(
                'properties' => array(
                    'classA' => '@ClassA',
                ),
            ),
        ));     
        $classA = $factory->create('ClassA');
    }
    
    public function testCyclicDependenciesWithSingleton(){
        $factory = new IoCFactory(array(
            'ClassA' => array(
                'properties' => array(
                    'classB' => '@ClassB',
                ),
                'singleton' => true,
            ),
            'ClassB' => array(
                'properties' => array(
                    'classA' => '@ClassA',
                ),
            ),
        ));
        $classA = $factory->create('ClassA');
        $classB = $factory->create('ClassB');
        $this->assertInstanceOf('ClassB', $classA->classB);
        $this->assertInstanceOf('ClassA', $classB->classA);
        $this->assertSame($classB->classA, $classA);
    }
}

