# phprs 
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/caoym/phprs-restful)
[![Build Status](https://travis-ci.org/caoym/phprs-restful.svg)](https://travis-ci.org/caoym/phprs-restful)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/caoym/phprs-restful/master/LICENSE)

phprs是一款轻量、类jax-rs、实用的PHP框架，用于快速开发RESTful Web Services.[English](https://github.com/caoym/phprs-restful/blob/master/README.md)

[Wiki](https://github.com/caoym/phprs-restful/wiki/English)

# 要求

   PHP5.4+

## Hello World

1. 在your-project-dir/apis/下新建 HelloWorld.php 

    ```PHP
    /**
     * @path("/hw")
     */
    class HelloWorld
    {
        /** 
         * @route({"GET","/"})
         */
        public function doSomething() {
            return ['msg'=>'Hello World!'];
        }
    }
    ```
2. 浏览器访问 http://your-domain/hw/

    ```JSON
    {
        "msg":"Hello World!"
    }
    ```
    
## 发生了什么

   回过头看HelloWorld.php，特殊的地方在于注释（@path，@route），没错，框架**通过注释获取路由信息和绑定输入输出**。但不要担心性能，注释只会在类文件修改后解析一次。更多的@注释后面会说明。

## 示例 

   这是一个订单管理类。

```PHP
/**
 * @path("/orders/")
 */
class Orders
{
    /** 
    * 获取所有订单
     * @route({"GET","/"})
     * @return({"body"}) 此注释表示将函数返回值作为body输出
     */
    public function getAllOrders() {
        return Sql::select('*')->from('orders')->get($this->db);//数组默认将被转换成json输出
    }
    /** 
     * 获取指定的订单信息
     * @route({"GET","/*"}) *是通配符，匹配任意/orders/的子目录
     * @param({"id", "$.path[1]"})  提取路径中的第二节作为参数$id，如/orders/123中的123
      * @return({"body"})
     */
    public function getOrderById($id) {
        return Sql::select('*')->from('orders')->where('id=?',$id)->get($this->db);
    }
    
    /** 
     * 创建订单
     * @route({"POST","/"}) 
     * @param({"goods_info", "$._POST.goods"})
     * @return({"body"})
     */
    public function createOrder($goods_info){
        $order_id = Sql::insertInto('orders')->values($goods_info)->exec($this->db)->lastInsertId();
        return ['order_id'=>$order_id];
    }
    /**
     * @property 依赖注入点，可通过配置指定$db的实例
     */
    public $db;
}
```

## 特性

1.  **灵活的路由定义**

    PHPRS 通过 `@route` 定义路由.

        @route({"GET","/patha"})                  |   GET     | /patha, /patha/...
        ------------------------------------------+-----------+---------------------
        @route({"*","/patha"})                    |   GET     | /patha
                                                  |   POST    |
                                                  |   PUT     |
                                                  |   DELETE  |
                                                  |   HEAD    |
                                                  |   ...     |
        ------------------------------------------+-----------+---------------------
        @route({"GET","\patha\*\pathb"})          |   GET     | /patha/xxx/pathb
        ------------------------------------------+-----------+---------------------
        @route({"GET", "func1?param1=1&param2=2"})|   GET     | /func1?param1=1&param2=2&...
                                                  |           | /myapi/func1?param2=2&param1=1&...

2.  **双向参数绑定**

    注释: `@param`, `@return`,`@throws` 可用于双向绑定http的输入输出.

       
        ------------------------------------------+-----------------------------
        @param({"arg0","$._GET.arg0"})            | $arg0 = $_GET['arg0']
        ------------------------------------------+-----------------------------
        @param({"arg1","$.path[1]"})              | $arg1 = explode('/', REQUEST_URI)[1]
        ------------------------------------------+-----------------------------
        @return({"cookie","token","$arg2"})       | setcookie('token', $arg2)
        function testCookie(&$arg2)               |
        ------------------------------------------+-----------------------------
        @return({"body"})                         | use function return as http response body
        ------------------------------------------+-----------------------------
        @throws({"MyException",                   | try{}
            "res",                                | catch(MyException) {
            "400 Bad Request",                    |   header("HTTP/1.1 400 Bad Request");body(["error"=>"my exception"]);}
            {"error":"my exception"}}) 

3. **Api 缓存**

    通过`@cache` 启用指定接口的缓存功能. 当两次请求的参数（只限通过@注释的参数）一致时，可命中缓存。

        ----------------------------------+-----------------------------
        @cache({"ttl",3600})              | 固定有效时间缓存
        ------------------------------------------+-----------------------------
        @cache({"checker", "$checker"})   | 动态缓存失效策略的缓存. 
                                          | $checker被用于判断缓存是否任然有效, 当缓存命中时， $checker($data, $create_time)将被调用，起返回false表示缓存失效, $checker的实现可以参考FileExpiredChecker.

4. **依赖注入**

   通过 `@property` 定义依赖注入点
   phprs 实例化接口类时，根据conf.json中的定义，对接口进行依赖注入。以下是conf.json的示例：
    ```JSON
    {
       "Orders":{
            "properties": {
                "db":"@db"
            }
       },
       "db":{
            "singleton":true,
            "class":"PDO",
            "pass_by_construct":true,
            "properties":{
                "dsn":"mysql:host=127.0.0.1;dbname=testdb;",
                "username":"test",
                "passwd":"test"  		
            }
       }
    }
    ```

5. **自动文档生成**

   自动生成的文档类似下图:
   
   ![](https://raw.githubusercontent.com/caoym/phprs-restful/master/doc/doc_sample_1.png)

6. **Hook**

  实现Hook的方式和接口一样，通过@注释定义路由、参数绑定和依赖输入等.
   
7. **ezsql**
 
   phprs还包含一个简单的数据库操作类.
   
## 快速开始

   https://github.com/caoym/phprs-restful/wiki/%E5%BF%AB%E9%80%9F%E5%BC%80%E5%A7%8B

## 为什么又多一个框架

   开源PHP框架已经很多，仅仅是适合开发Restful的PHP框架就不乏优秀的，如laravel、anandkunal/ToroPHP、peej/tonic等等，那么为何还有phprs呢？自己总结一下主要有几个原因：

   1. **总是一起做的事情，就要放在一起做**：比如通常实现一个新功能，需要：定义接口、实现代码、配置路由、写接口文档，而大部分框架需要程序猿们切换到不同地方（场景）去做这些事情；反过来，不相关的，却又通常耦合在一起，比如路由定义。

   2. **低侵入性**：大部分框架都过多的干涉了上层的代码，比如需要通过继承使用框架的功能，需要使用框架的设计模式比如MVC。虽然通常这些限制是有益的，特别是对于大型项目。但这些限制并不一定要框架提供，对于“轻+简单”为优势的框架，我更希望它使用起来像”没有框架“。

   3. **简单**：这一点其实与上面两点重复，但重要的事情要说三遍。

## 社区

   欢迎加入 QQ群 185193529

## TODO

1. 支持注入远程服务
2. 用户自定义 annotation
3. 更高大上的文档生成
