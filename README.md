# PhpBoot

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/caoym/phpboot/master/LICENSE)
[![Package version](http://img.shields.io/packagist/v/caoym/phpboot.svg)](https://packagist.org/packages/caoym/phpboot)
[![Documentation Status](https://readthedocs.org/projects/phpboot/badge/?version=latest)](http://phpboot.readthedocs.io/zh/latest/?badge=latest)
[![Build Status](https://travis-ci.org/caoym/phpboot.svg?branch=master)](https://travis-ci.org/caoym/phpboot)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/caoym/phpboot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/caoym/phpboot/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/caoym/phpboot/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/caoym/phpboot/?branch=master)

> phprs-restful 2.x is renamed to PhpBoot, and incompatible with 1.x. You can get the old version from [phprs-restful v1.x](https://github.com/caoym/phprs-restful/tree/v1.2.4)

[查看中文说明](https://github.com/caoym/phpboot/blob/master/README.zh.md)

**[PhpBoot](https://github.com/caoym/phpboot)** is an easy and powerful PHP framework for building RESTful/Microservices APIs.

## Specialities

PhpBoot provides mainstream features, such as IOC, AOP, ORM, Validation, etc. But the most striking features are:

### 1. Designing object-oriented APIs

**WITHOUT**  PhpBoot:

```PHP
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

**WITH**  PhpBoot:

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
        $total = ...
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
Read more: [phpboot-example](https://github.com/caoym/phpboot-example)。
    
### 2. Swagger

PhpBoot can automatically generate Swagger JSON，which can be rendered as document by Swagger UI like this：

<div>
<img src="https://github.com/caoym/phpboot/raw/master/docs/_static/WX20170809-184015.png" width="60%">
</div>

Read more: [Online Demo](http://swagger.phpboot.org/?url=http://example.phpboot.org/docs/swagger.json)

### 3. RPC

Call the remote Books with RPC:

```PHP
$books = $app->make(RpcProxy::class, [
        'interface'=>Books::class, 
        'prefix'=>'http://x.x.x.x/'
    ]);
    
$books->findBooks(...);
```

Concurrent call RPC：

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

Read more: [RPC](http://phpboot.org/zh/latest/advanced/rpc.html)

### 4. IDE friendly 

<div>
<img src="https://github.com/caoym/phpboot/raw/master/docs/_static/db.gif">
</div>

## Features
   
   * [Route](http://phpboot.org/zh/latest/basic/route.html)
   * [Parameters binding ](http://phpboot.org/zh/latest/basic/params-bind.html)
   * [Validation](http://phpboot.org/zh/latest/basic/validation.html)
   * [Dependency Injection(IOC)](http://phpboot.org/zh/latest/basic/di.html)
   * [DB](http://phpboot.org/zh/latest/basic/db.html)
   * [ORM](http://phpboot.org/zh/latest/advanced/orm.html)
   * [Docgen(Swagger)](http://phpboot.org/zh/latest/advanced/docgen.html)
   * [RPC](http://phpboot.org/zh/latest/advanced/rpc.html)
   * [AOP(Hook)](http://phpboot.org/zh/latest/advanced/hook.html)
   
## Installation

   1. Install composer

	   ```
	   curl -s http://getcomposer.org/installer | php
	   ```
       
   2. Install PhpBoot
   
       ```
       composer require "caoym/phpboot"
       ```
       
   3. index.php
       
       ```PHP
       <?php
       require __DIR__.'/vendor/autoload.php';
      
       $app = \PhpBoot\Application::createByDefault(__DIR__.'/config/config.php');
       $app->loadRoutesFromPath(__DIR__.'/App/Controllers');
       $app->dispatch();
       ```
   
## Help & Documentation

   * **[Documentation](http://phpboot.org)**
   * **[中文文档](http://phpboot.org)**
   * Email: caoyangmin@gmail.com
   
