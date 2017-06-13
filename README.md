# phprs 
[![Build Status](https://travis-ci.org/caoym/phprs-restful.svg?branch=master)](https://travis-ci.org/caoym/phprs-restful)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/caoym/phprs-restful/master/LICENSE)
[![Package version](http://img.shields.io/packagist/v/caoym/phprs-restful.svg)](https://packagist.org/packages/caoym/phprs-restful)

Lightweight, easy-to-use and jax-rs-like for RESTful Web Services.[中文文档](https://github.com/caoym/phprs-restful/blob/master/README.CN.md)

[Wiki](https://github.com/caoym/phprs-restful/wiki/English)

# Requirements
PHP5.4+

## Hello World
1. Put HelloWorld.php in your-project-dir/apis/

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
2. open http://your-domain/hw/

    ```JSON
    {
        "msg":"Hello World!"
    }
    ```
    
## What happened
See HelloWorld.php, the annotations like @path，@route are used to define routers. Phprs also use annotations for two-way parameter binding, dependency injection, etc. 

## Examples 
"orders manage"

```PHP
/**
 * @path("/orders/")
 */
class Orders
{
    /** 
     * @route({"GET","/"})
     * @return({"body"})
     */
    public function getAllOrders() {
        return Sql::select('*')->from('orders')->get($this->db);
    }
    /** 
     * @route({"GET","/*"})
     * @param({"id", "$.path[1]"})
      * @return({"body"})
     */
    public function getOrderById($id) {
        return Sql::select('*')->from('orders')->where('id=?',$id)->get($this->db);
    }
    
    /** 
     * @route({"POST","/"})
     * @param({"goods_info", "$._POST.goods"})
     * @return({"body"})
     */
    public function createOrder($goods_info){
        $order_id = Sql::insertInto('orders')->values($goods_info)->exec($this->db)->lastInsertId();
        return ['order_id'=>$order_id];
    }
    /**
     * Instance of class \PDO
     * @property 
     */
    public $db;
}
```

## Features

1.  **Flexible routes**

    PHPRS use `@route` to define routes.

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
        @route({"GET","\func1?param1=1&param2=2"})|   GET     | /func1?param1=1&param2=2&...
                                                  |           | /func1?param2=2&param1=1&...

2.  **Two-way parameter binding**

    Annotations: `@param`, `@return`,`@throws` is used to bind variables between function parameters and http request or response.

       
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
            "400 Bad Request",                    |   header('HTTP/1.1 400 Bad Request');
            {"error":"my exception"}})            |   body('{"error":"my exception"}');
                                                  | }

3. **Api cache**

    Use `@cache` to enable cache for this method. If all params of the method are identical, the following calls will use cache.

        ----------------------------------+-----------------------------
        @cache({"ttl",3600})              | set cache as fixed time expire, as ttl 1 hour.
        ----------------------------------+-----------------------------
        @cache({"checker", "$checker"})   | Use dynamic strategy to check caches. 
                                          | $checker is set in method, and will be invoked to check cache expired with $checker($data, $create_time), for examples use $check = new FileExpiredChecker('file.tmp'); to make cache invalidated if file.tmp modified.

4. **Dependency Injection**

    Use `@property` to inject dependency
    phprs create API class and inject dependency from conf.json, which is looks like
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

5. **Document automatic generation**

   The document is looked like:
   ![](https://raw.githubusercontent.com/caoym/phprs-restful/master/doc/doc_sample_1.png)

6. **Hook**

   The implement of a hook is the same as API.
   
7. **ezsql**

   An easy-to-use and IDE friendly SQL builder. Object-oriented SQL. @see https://github.com/caoym/ezsql
   
## Quick start
https://github.com/caoym/phprs-restful/wiki/Quick-start

## TODO

1. Inject Remote service support
2. User-defined annotations
3. Graceful auto document 
