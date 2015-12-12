<?php
use caoym\util\IoCFactory;
use caoym\util\Logger;

/***************************************************************************
 *
 * Copyright (c) 2014 . All Rights Reserved
 *
 **************************************************************************/

require_once __DIR__.'/../../../lib/caoym/AutoLoad.php';

Logger::$writer = Logger::$to_echo;
/**
 * RestfulApiContainer test case.
 */
class RestfulApTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests RestfulApiContainer->invoke()
     */
    public function testInvoke()
    {
        $factory = new IoCFactory(array(
            'caoym\phprs\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'func',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'func');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func?a=1&b=2',
            ],
            'header'=>'test header',
            'arg0'=>'test arg0',
            'arg1'=>'test arg1',
        ];
       
        $res_buffer = array(); // 输出数据缓存
        $sender = array(
            'status' => function () use(&$res_buffer)
            {
                $res_buffer[] = [
                    'status',
                    func_get_args(),
                ];
            },
            'header' => function () use(&$res_buffer)
            {
                $res_buffer[] = [
                    'header',
                    func_get_args(),
                ];
            },
            'cookie' => function () use(&$res_buffer)
            {
                $res_buffer[] = [
                    'cookie',
                    func_get_args(),
                ];
            },
            'body' => function () use(&$res_buffer)
            {
                $res_buffer[] = [
                    'body',
                    func_get_args(),
                    
                ];
            },
        );
        $res= new \caoym\phprs\Response($sender); //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
        $check = array(
            array('status',array('test arg1')),
            array('header',array('test arg1')),
            array('header',array('return $arg2')),
            array('header',array('const header')),
            array('cookie',array('token','test arg1','3000','return $arg2')),
            array('body',array(array('arg0'=>'test arg0', 'arg1'=>'test arg1', 'arg2'=>'return $arg2', 'arg3'=>'arg3 default'))),
            );
        $route($req, $res);
       
        $this->assertEquals($check, $res_buffer);
    }
    /**
     * 无效的参数绑定: 源不存在
     */
    public function testInvalidBind1()
    {
        $this->setExpectedException('Exception','$.unknown not found in request');
        
        $factory = new IoCFactory(array(
            'caoym\phprs\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcInvalidBind1',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcInvalidBind1');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func1',
            ],
            'header'=>'test header',
        ];
        $res= new \caoym\phprs\Response();    //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
        
        $route($req, $res);
    }
    
    /**
     * 无效的参数绑定: 绑定的变量不存在
     */
    public function testInvalidBind2()
    {
        $this->setExpectedException('Exception', 'MyTestApi::funcInvalidBind2 param: unknown not found');
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcInvalidBind2',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcInvalidBind2');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func2',
            ],
            'header'=>'test header',
        ];
        $res= new \caoym\phprs\Response();    //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
    
        $route($req, $res);
    }
    
    /**
     * 无效的参数绑定: 有变量未被绑定, 且没有默认值
     */
    public function testInvalidBind7()
    {
        $this->setExpectedException('Exception', 'MyTestApi::funcInvalidBind7 param: arg1 not be bound');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcInvalidBind7');
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcInvalidBind7',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func7',
            ],
            'header'=>'test header',
        ];
        $res= new \caoym\phprs\Response();    //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
    
        $route($req, $res);
    }
    
    /**
     * 无效的参数绑定: 返回值不是引用
     */
    public function testInvalidBind3()
    {
        $this->setExpectedException('Exception', 'MyTestApi::funcInvalidBind3 param: arg0 @return must be a reference');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcInvalidBind3');
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcInvalidBind3',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func3',
            ],
            'header'=>'test header',
        ];
        $res= new \caoym\phprs\Response();    //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
    
        $route($req, $res);
    }
    
    /**
     * 无效的参数绑定: 返回值不存在
     */
    public function testInvalidBind4()
    {
        $this->setExpectedException('Exception', 'MyTestApi::funcInvalidBind4 param: arg1 not found');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcInvalidBind4');
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcInvalidBind4',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func4',
            ],
            'header'=>'test header',
        ];
        $res= new \caoym\phprs\Response();    //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
    
        $route($req, $res);
    }
    
    /**
     * 无效的参数绑定: 返回值不存在, 有默认值
     */
    public function testInvalidBind5()
    {
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcInvalidBind5');
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcInvalidBind5',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func5',
            ],
            'header'=>'test header',
        ];
        $res= new \caoym\phprs\Response();    //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
    
        $route($req, $res);
    }
    
    /**
     * 绑定异常, 异常类型完全匹配
     */
    public function testWithException1()
    {
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__) . '/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcWithException1',
                )
            )
        ));
        $route = $factory->create('caoym\phprs\Router');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcWithException1');
        //$route = $factory->create('caoym\phprs\Router');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func8',
            ],
            'header' => 'test header',
        ];
         
        $res_buffer=array(); // 输出数据缓存
        $sender = array(
            'status' => function () use(&$res_buffer)
            {
                $res_buffer[] = [
                    'status',
                    func_get_args(),
                ];
            },
            'body' => function () use(&$res_buffer)
            {
                $res_buffer[] = [
                    'body',
                    func_get_args(),
                ];
            },
        );
        $res= new \caoym\phprs\Response($sender); //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
        $check = array(
            array('status', array('500')),
            array('body',array('MyException')),
        );
        $route($req, $res);
         
        $this->assertEquals($check, $res_buffer);
    }
    
    /**
     * 绑定异常, 父类匹配
     */
    public function testWithException2()
    {
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funcWithException2',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //$route = new \caoym\phprs\Router(dirname(__FILE__).'/rest_test', 'MyTestApi', 'funcWithException2');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func9',
            ],
            'header'=>'test header',
        ];
         
        $res_buffer=array(); //输出数据缓存
        $sender = array(
            'status'=>function ()use(&$res_buffer){$res_buffer[]=['status',func_get_args()];},
            'body'=>function ()use(&$res_buffer){$res_buffer[]=['body',func_get_args()];},
        );
        $res= new \caoym\phprs\Response($sender); //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
        $check = array(
            array('body', array('Exception')),
        );
        $route($req, $res);
        
        $this->assertEquals($check, $res_buffer);
    }
    /**
     * 测试绑定引用类型的请求
     */
    public function testReferenceReqParam()
    {
        $factory = new IoCFactory(array(
            'caoym\\phprs\\Router' => array(
                'properties' => array(
                    'api_path' => dirname(__FILE__).'/rest_test',
                    'apis' => 'MyTestApi',
                    'api_method' => 'funReferenceReqParam',
                ),
            ),
        ));
        $route = $factory->create('caoym\phprs\Router');
        //输入数据
        $req_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/testapi/func10',
            ],
            'header' => 'test header'
        ];
         
        $res_buffer=array(); //输出数据缓存
        $sender = array(
            'status'=>function ()use(&$res_buffer){$res_buffer[]=['status',func_get_args()];},
            'body'=>function ()use(&$res_buffer){$res_buffer[]=['body',func_get_args()];},
        );
        $res= new \caoym\phprs\Response($sender); //替换默认的输出方式, 以便记录输出结果
        $req = new \caoym\phprs\Request($req_buffer);
        $route($req, $res);
        
        $check_buffer = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => 'funReferenceReqParam arg0',
            ],
            'arg1'=>array('arg1'=>'funReferenceReqParam arg1'),
            'header'=>'test header',
        ];
        $res = $req->toArray();
        unset($res['router']);
        unset($res['path']);
        $this->assertEquals($check_buffer, $res);
    }
}

