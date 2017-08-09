# PhpBoot

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/caoym/phpboot/master/LICENSE)
[![Package version](http://img.shields.io/packagist/v/caoym/phpboot.svg)](https://packagist.org/packages/caoym/phpboot)
[![Build Status](https://travis-ci.org/caoym/phpboot.svg?branch=master)](https://travis-ci.org/caoym/phpboot)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/caoym/phpboot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/caoym/phpboot/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/caoym/phpboot/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/caoym/phpboot/?branch=master)

> phprs-restful 2.x 版本改名为PhpBoot。当前版本由于改动较大, 与1.x 版本不兼容。下载1.x版本请前往 [phprs-restful v1.x](https://github.com/caoym/phprs-restful/tree/v1.2.4)

**[PhpBoot](https://github.com/caoym/phpboot)** 是为快速开发 RESTful API 设计的PHP框架。它可以帮助开发者更聚焦在业务本身, 而将原来开发中不得不做, 但又重复枯燥的事情丢给框架, 比如编写接口文档、参数校验和远程调用代码等。

## 特色

PhpBoot 框架提供主流的特性, 如ORM、依赖注入等。 这些特性都经过精心设计或选择(有些是第三方开源代码,如 PHP-DI), 即保证极简单的使用, 又需和框架整体的风格保持一致。但和其他框架相比较, PhpBoot 最显著的特色是:

### 1. 以面向对象的方式编写接口

你肯定看到过这样的代码:

```PHP
// **不用** PhpBoot 的代码
class BookController
{
    public function findBooks(Request $request)
    {
        $name = $request->get('name');
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        ...
        return new Response(['total'=>$total, 'data'=>$books]);
    }
    
    public function createBook(Request $request)
    ...
}
```

很多主流框架都需要用类似代码编写接口。但这种代码的一个问题是, 方法的输入输出隐藏在实现里, 这不是通常我们提倡的编码方式。如果你对代码要求更高, 你可能还会实现一层 Service 接口, 而在 Controller 里只是简单的去调用 Service 接口。而使用 PhpBoot, 你可以用更自然的方式去定义和实现接口。上面的例子, 在 PhpBoot 框架中实现是这样的:

```PHP
/**
 * @path /books/
 */
class Books
{
    /**
     * @route GET /
     * @return Book[]
     */
    public function findBooks($name, &$total=null, $offset=0, $limit=10)
    {
        ...
        return $books;
    }
  
    /**
     * @route POST /
     * @param Book $book {@bind request.request} bind $book with http body
     * @return string id of created book
     */
    public function createBook(Book $book)
    {
        $id = ... 
        return $id;
    }
}
```

上面两份代码执行的效果是一样的。可以看到 PhpBoot 编写的代码更符合面向对象编程的原则, 以上代码完整版本请见[phpboot-example](https://github.com/caoym/phpboot-example)。
    
### 2. 轻松支持 Swagger

[Swagger](https://swagger.io)是目前最流行的接口文档框架。虽然很多框架都可以通过扩展支持Swagger, 但一般不是需要编写很多额外的注释, 就是只能导出基本的路由信息, 而不能导出详细的输入输出参数。目前只有 PhpBoot 可以在不增加额外编码负担的情况下, 更多内容请见[文档](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/wen-dang-shu-chu.html)和[在线 Demo](http://118.190.86.50:8007/index.html?url=http://118.190.86.50:8009/docs/swagger.json)。

<div>
<img src="https://github.com/caoym/phpboot-book/raw/master/assets/WX20170809-184015.png" width="60%">
</div>

### 3. 简单易用的分布式支持

使用 PhpBoot 可以很简单的构建分布式应用。通过如下代码, 即可轻松远程访问上面示例中的 Books 接口:

```PHP
$books = $app->make(RpcProxy::class, [
        'interface'=>Books::class, 
        'prefix'=>'http://x.x.x.x/'
    ]);
    
$books->findBooks(...);
```

同时还可以方便的发起并发请求, 如:


```PHP
$res = MultiRpc::run([
    function()use($service1){
        return $service1->doSomething();
    },
    function()use($service2){
        return $service2->doSomething();
    },
]);
```

更多内容请查看[文档](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/fen-bu-shi.html)

### 4. IDE 友好  

IDE 的代码提示功能可以让开发者轻松不少, 但很多框架在这方面做的并不好, 你必须看文档或者代码, 才能知道某个功能的用法。PhpBoot 在一开始就非常注重让代码保持IDE友好, 经可能让所有代码都能有正确的代码提示。比如下图是 DB 库在 PhpStorm IDE 下的使用:

<div>
<img src="https://github.com/caoym/phpboot-book/raw/master/assets/db.gif">
</div>

## 主要特性
   
   * [基于Annotation的路由定义](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/lu-you.html)
   * [接口参数双向绑定](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/can-shu-bang-ding.html)
   * [Validation](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/can-shu-xiao-yan.html)
   * [依赖注入](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/yi-lai-zhu-ru.html)
   * [DB](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/shu-ju-ku.html)
   * [ORM](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/orm.html)
   * [自动文档和接口工具](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/wen-dang-shu-chu.html)
   * [分布式支持(RPC)](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/fen-bu-shi.html)
   * [Hook](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/hook.html)
   * [工作流引擎(开发中...)](https://caoym.gitbooks.io/phpboot/content/ji-ben-te-xing/gong-zuo-liu.html)
 
   
## 安装和配置

   1. 安装 composer (已安装可忽略)
   
       ```
       curl -s http://getcomposer.org/installer | php
       ```
       
   2. 安装 PhpBoot
   
       ```
       composer require "caoym/phpboot"
       ```
       
   3. index.php 加载 PhpBoot
       
       ```PHP
       <?php
       require __DIR__.'/vendor/autoload.php';
      
       $app = \PhpBoot\Application::createByDefault(__DIR__.'/config/config.php');
       $app->loadRoutesFromPath(__DIR__.'/App/Controllers');
       $app->dispatch();
       ```
   
## 帮助和文档

   * **[在线文档](https://caoym.gitbooks.io/phpboot/content/)**
   * **QQ 交流群:185193529**
   * 本人邮箱 caoyangmin@gmail.com
   



