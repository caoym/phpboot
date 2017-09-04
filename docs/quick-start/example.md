# 示例

下面将通过编写一个简单的图书管理系统接口，演示 PhpBoot 的使用。完整的示例可在[这里](https://github.com/caoym/phpboot-example)下载。

## 关于 RESTful

当前 RESTful 已经不是新鲜的名词了，抛开抽象的定义，我认为一个通俗的解释可以是：按文件系统的方式去设计接口，即把接口提供的功能，当做是对“目录”的“操作”。比如一个登录接口，按 RESTful 设计，可以是```POST /tokens/```，即把登录，当做新建一个令牌，这里的```/tokens/```就是“目录”，```POST```就是对目录的“操作”。关于 RESTful 比较准确的定义，可以看[这里](https://www.ibm.com/developerworks/cn/webservices/ws-restful/index.html)。关于 RESTful 最佳实践，可以看[这里](http://restpatterns.mindtouch.us/HTTP_Methods/MOVE)。

## 示例接口

下面我将演示如何用 PhpBoot 编写一组“图书管理”接口，这些接口包括：

|接口名|METHOD|URI|请求示例|响应示例|
|:--|:--|:--|:--|:--|
|查询图书|GET| /books/| GET /books/?name=php&offset=0&limit=1|{<br>  "total": 0,<br>  "data": [<br>    {<br>      "id": 0,<br>      "name": "string",<br>      "brief": "string",<br>      "pictures": [<br>        "string"<br>      ]<br>    }<br>  ]<br>}|
|获取图书详情|GET| /books/{id} | GET /books/1|{<br>  "id": 0,<br>  "name": "string",<br>  "brief": "string",<br>  "pictures": [<br>    "string"<br>  ]<br>}|
|新建图书|POST| /books/|POST /books/<br><br>{<br>  "id": 0,<br>  "name": "string",<br>  "brief": "string",<br>  "pictures": [<br>    "string"<br>  ]<br>}|123|
|删除图书| DELETE| /books/{id}|DELETE /books/1| |

## 项目目录结构

+ app
    * Controllers
        * **Books.php** _接口实现_
    * Entities
        * **Book.php** _数据实体定义_
+ config
    + **config.php** _配置_
+ public
    + **index.php** _入口_
+ vendor _依赖包_
    
## 入口

index.php 作为项目入口， 通常只需要指定配置文件和 Controllers 目录的路径即可。最终项目对外提供的接口， 由不同的 Controllers 的实现类提供。

```php
<?php
require __DIR__.'/../vendor/autoload.php';

use PhpBoot\Application;

$app = Application::createByDefault(__DIR__.'/../config/config.php');
//扫描 Controllers 目录，自动加载所有路由
$app->loadRoutesFromPath( __DIR__.'/../App/Controllers', 'App\\Controllers');
//执行请求
$app->dispatch();

```

## 接口实现

### 定义 Book 实体

为了在不同接口间共享“图书信息”的数据结构，我们定义一个实体如下：

```php
class Book
{
    /**
     * @var int
     */
    public $id;
    /**
     * 书名
     * @var string
     */
    public $name='';

    /**
     * 简介
     * @var string
     */
    public $brief='';

    /**
     * 图片url
     * @var string[]
     */
    public $pictures=[];
}
```

### 定义 Controller

这里定义了 Books 类作为 Controller，后续接口将实现在此 Books 类中。

```php
/**
 * 提供图书管理接口
 * @path /books/
 */
class Books
{

}
```

上述代码中的注释```@path /books/``` 表示 Books 下所有接口，都使用/books/ 作为前缀。

### 查询图书接口

```php
/**
 * 查询图书
 *
 * @route GET /
 *
 * @param string $name  查找书名
 * @param int $offset 结果集偏移 {@v min:0}
 * @param int $limit 返回结果最大条数 {@v max:1000}
 * @param int $total 总条数
 * @throws BadRequestHttpException 参数错误
 * @return Book[] 图书列表
 */
public function findBooks($name, &$total, $offset=0, $limit=100)
{
    $total = 1;
    return [new Book()];
}
```

为便于理解，这段代码只是返回了一组固定的数据，真实场景下应该还会访问数据库或者缓存。下面将说明这段代码的工作方式：

1.  ```@route``` 定义了此接口的路由，即 ```GET /books/```(加上了@path 定义的前缀)。
2.  ```@param``` 定义了接口的请求参数和类型，如果不声明```@param```, 接口参数将从函数定义中提取， 如果函数定义中没有申明参数类型，则参数类型被认为是 mixed。
3.  ```@v``` 定义了参数的取值范围，若不指定，框架将只会校验请求中的参数类型， 即如果参数是类型是 int，则请求中参数必须是可以转换成 int 的类型，如 123 或者"123"等，否则会返回 400 错误
4.  函数的返回值将被 json_encode 后输出到 body。如果函数的参数中没有引用类型（引用类型的参数被认为是输出，而不是输入），return 在 json_encode 后即被当做 body 输出，否则 return 将被赋值给 body 中的 "data"。
5.  ```&$total``` 是引用类型的参数，会被最为输出，默认输出到 body 中同名变量中。如这个接口中，最终输出的 body 形式如下：

	```json
	{
	    "total": 1, //来自 &$total
	    "data": [   //来自 return
	        {
	            "id": 0,
	            "name": "string",
	            "brief": "string",
	            "pictures": [
	                "string"
	            ]
	        }
	    ]
	}
	```

6. 如果希望将 return 输出到其他位置，或者不使用默认的输入参数绑定方式，可以使用```@bind```, 比如 ```@return Book[] 图书列表 {@bind response.content.books} ``` 将使 return 结果绑定在 json 的 "books" 上，而不是默认的 "data"。
7. ```$offset=0, $limit=100```定义了默认值，如果请求中不包含这些参数，将使用默认值。
8. 注释 ```@return Book[]``` 和 ```@throws BadRequestHttpException``` 并不会对接口的返回有任何影响， 但是会影响文档输出和远程调用（RPC）。

### 获取图书详情接口

```php
/**
 * 获取图书
 * @route GET /{id}
 *
 * @param string $id 指定图书编号
 *
 * @throws NotFoundHttpException 图书不存在
 *
 * @return Book 图书信息
 */
public function getBook($id)
{
    return new Book();
}
```

路由 ```@route GET /{id}``` 指定了 url 的 path 有一个变量```{id}```，变量的值可以通过函数参数 ```$id``` 获取


### 新建图书

```php
/**
 * 新建图书
 *
 * @route POST /
 * @param Book $book {@bind request.request} 这里将post的内容绑定到 book 参数上
 * @throws BadRequestHttpException
 * @return string 返回新建图书的编号
 */
public function createBook(Book $book)
{
    return '123';
}
```

1. 请求中的 json 会被框架自动转换成函数中需要的对象参数。
2. ```{@bind request.request}``` 表示用请求的 body 构造 $book 变量，若不指定@bind，默认是提取请求 body 中 "book" 字段构造 $book 变量，也就是说请求会是以下形式：

```json
{
    "book": {
            "id": 0,
            "name": "string",
            "brief": "string",
            "pictures": [
                "string"
            ]
        }
}
```

### 删除图书

```php
/**
 * 删除图书
 *
 * @route DELETE /{id}
 * @param string $id
 * @throws NotFoundHttpException 指定图书不存在
 * @return void
 */
public function deleteBook($id)
{
    
}

```

如果函数没有返回值，则响应的 http body 会是 ```void```， 而不是空字符串, 因为 基于PhpBoot 实现的接口，默认情况下，http body 总是 json，而空字符串并不是合法的 json。

## 更多

更多内容见:

* [完整示例代码](https://github.com/caoym/phpboot-example)

* [在线 Demo](http://swagger.phpboot.org/?url=http%3a%2f%2fexample.phpboot.org%2fdocs%2fswagger.json)。
