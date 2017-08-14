## 示例


下面将通过编写一个简单的图书管理系统接口，演示 PhpBoot 的使用。完整的示例可在[这里](https://github.com/caoym/phpboot-example)下载。

### 1. 目录结构

+ app
    * Controllers
        * **Books.php** _接口实现_
    * Entities
        * **Book.php** _数据实体定义_
+ config
    + **config.php** _配置_
+ db
    + phpboot-example.db _示例用到的 sqlite 数据库实例_
+ public
    + **index.php** _入口_
+ vendor _依赖包_
    
### 2. 入口

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

### 3. 接口实现

示例对外提供```GET /books/```接口，用于查找图书，返回图书列表。形式如下：

```
$ curl "http://localhost/books/?name=PHP&offset=0&limit=10"
[
   {
       "id": 1,
       "name": "PHP",
       "brief": "PHP 从入门到嫌弃",
       "pictures": []
   }
]
```

其对应的代码为：

```php
namespace App/Controllers;

/**
 * 图书管理接口示例
 *
 * @path /books
 */
class Books
{
   /**
    * 查找图书
    *
    * @route GET /
    *
    * @param string $name  查找书名
    * @param int $offset 结果集偏移 {@v min|0}
    * @param int $limit 返回结果最大条数 {@v max|1000}
    *
    * @return Book[] 图书列表 
    */
   public function findBooks($name, $offset=0, $limit=100)
   {
       return \PhpBoot\models($this->db, Book::class)
           ->where(['name'=>['LIKE'=>"%$name%"]])
           ->limit($offset, $limit)
           ->get();
   }
}
```

以上代码演示了 PhpBoot 的几项基本功能：

* 路由定义
通过 @path 和@route 定义路由。
    
* 参数绑定
通过 @param（如果没有注释，默认通过反射从方法定义里获取参数信息）绑定输入，当前示例中，@param 绑定了请求的 querystring 和实现方法的输入参数。

* 参数校验

通过 @v 申明参数的取值范围。

* ORM
```\PhpBoot\models()``` 方法根据实体类 Book()实例化 Model 对象，并提供基本的如 find、create、update、delete 等方法。


### 4. 项目配置

示例中用到了数据库， 通过 config.php 配置：

```php
<?php
return [
    'DB.connection'=> 'sqlite:/tmp/phpboot-example.db'
];
```





