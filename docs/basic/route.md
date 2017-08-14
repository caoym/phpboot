# 路由

PhpBoot 支持两种形式的路由定义， 分别是通过加载 Controller 类，分析 Annotation ，自动加载路由，和通过 Application::addRoute 方法手动添加路由。

## 1. 自动加载路由

你可以通过 Application::loadRoutesFromClass 或者 Application::loadRoutesFromPath 添加路由。框架扫描每个类的每个方法，如果方法标记了@route，将被自动添加为路由。被加载类的形式如下：

```php
/**
 * @path /books
 */
class Books
{
    /**
     * @route GET /{id}
     */
    public function getBook($id)
}
```
以上代码表示 http 请求 ```GET /books/{id}``` 其实现为 Books::getBook, 其中{id}为url 的可变部分。

### 1.1. @path 

**语法：** ```@path <prefix>```

标注在类的注释里，用于指定 Controller 类中所定义的全部接口的uri 的前缀。


### 1.2. @route

**语法：** ```@path <method> <uri>```

标注在方法的注释里，用于指定接口的路由。method为指定的 http 方法，可以是 GET、HEAD、POST、PUT、PATCH、DELETE、OPTION、DELETE。uri 中可以带变量，用{}包围。

## 2. 手动加载路由

你可以使用 Application::addRoute 手动加载路由，方法如下：

```php
$app->addRoute('GET', '/books/{id}', function(Request $request){
    $books = new Books();
    return $books->getBook($request->get('id'));
});

```

**需要注意的是，此方法添加的路由，将不能自动生成接口文档。**




