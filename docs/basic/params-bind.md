# 参数绑定

实现接口时，通常需要从 http 请求中提取数据，作为方法的输入参数，并将方法的返回值转换成 http 的输出。参数绑定功能即可以帮你完成上述工作。

## 1. 输入绑定

### 1.1. 根据方法定义绑定


默认情况下，框架会从http请求中提取和方法的参数名同名的变量，作为函数的参数。比如：

```php
/**
 * @route GET /books/
 */
public function getBooks($offsit, $limit)
```
上述代码，对应的 http 请求形式为 ```GET /books/?offsit=0&limit=10```。在此默认请求下：

* 如果路由 uri 中定义了变量，参数将优先选取 uri 变量。如：

 ```php
 /**
  * @route GET /books/{id}
  */
 public function getBook($id)
 ```
 其中 $id 取自 uri。
 
* 对于没有 BODY 的 http 请求（GET、HEAD、OPTION、DELETE），参数来自 querystring 。

* 其他请求（POST、PUT、OPTION），参数先取 querystring，如果没有，再取 BODY。

### 1.2. @param

如果在方法的注释中，标注了 @param，就会有用 @param 的绑定信息覆盖默认来自函数定义的绑定信息。@param 可以指定变量的类型，而原函数定义中只能在参数是数组或者对象时才能指定类型。@param 的语法为标准 PHP Document 的语法。

```php
/**
 * @route GET /books/
 * @param int $offsit
 * @param int $limit
 */
public function getBooks($offsit, $limit)

```
以上代码，除了绑定变量外，还指定了变量类型，即如果输入值无法转换成 int，将返回 400 BadRequest 错误。未指定@param 时，参数的类型默认为 mixed。

### 1.3. 输入对象参数

输入参数除了是原生类型外，还可以是对象（这里我们把只有属性和 get、set 方法的对象，称为实体（Entity））。如：

```php
/**
 * @route POST /books/
 * @param Book $book {@bind request.request} 将$_POST 内容转换成Book实例
 */
public function createBook(Book $bok)
```

其中 Book 的的定义：

```php
/**
 * 图书信息
 */
class Book
{
    /**
     * @var int
     * @v optional
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
     * @v lengthMax:200
     */
    public $brief='';

    /**
     * 图片url
     * @var string[]
     */
    public $pictures=[];
}
```
框架对 http 请求到实体的转换，有一套自己的逻辑：
* @var 指定属性的类型，如果类型不匹配，实例化将抛出 InvalidArgumentException 异常
* 如果不标注 @var，则默认类型为mixed
* 如果属性有默认值，表示此属性可选，否则认为此属性必选
* 支持 @v 定义校验规则
* 实体可以嵌套


### 1.4. 参数默认值

如果想指定某个输入参数可选，只需给方法参数设置一个默认值。比如:

```php
/**
 * @route GET /books/
 * @param int $offsit
 * @param int $limit
 */
public function getBooks($offsit=0, $limit=10)
```
**注意：php 方法的默认参数, 必须放在方法的最后**

## 2. 输出绑定

### 2.1. 绑定return

默认情况下，函数的返回值将 jsonencode 后，作为 body 输出。如

```php
/**
 * @route GET /books/{id}
 */
public function getBook($id)
{
    return ['name'=>'PhpBook', 'desc'=>'PhpBook Document'];
}
```
curl 请求将得到以下结果

```php
$ curl "http://localhost/books/1"
{
    "name": "PhpBook",
    "desc": "PhpBook Document"
}
```

**注意，这里为便于演示，直接在方法中返回了数组（其实这在其他语言里算对象），但你应该为这种返回定义一个类，首先，有很多改善代码质量的理由鼓励使用对象替代这类数组，其次在自动生成文档时，这类数组无发被结构化描述。**

### 2.2. 绑定引用参数

如果方法的参数是引用类型，则这个参数将不会从请求中获取，而是作为输出。比如：

```php
/**
 * @route GET /books/
 * @param int $offsit
 * @param int $limit
 * @return Books[]
 */
public function getBooks($offsit=0, $limit=10, &$total)
{
    $total = 1;
    return [new Books()];
}

```
curl 请求将得到以下结果

```
$ curl "http://localhost/books"
{
    "total": 1,
    "data": [
        {
            "name":null, 
            "desc":null
        }
    ]
}
```

可以看到，$total 输出到了 http body 中。 **注意：当接口存在引用参数时，接口的返回值将会被默认绑定到response.content.data，效果和声明{@bind response.content.data}一致。**
 
## 3. @bind

通过@bind，可以改变默认的绑定关系，将参数与其他输入项绑定，如：

```php
/**
 * @route GET /books/
 * @return Books[] {@bind response.content.books}
 */
public function getBooks($offsit=0, $limit=10, &$total)
```
表示将返回绑定到响应 body 的 books 变量（响应默认是 json）。


### 3.1. 绑定输入

* **请求Body:** request.request
* **Query String:** request.query
* **Cookie：** request.cookies
* **请求Header：**request.headers
* **文件：**request.files

### 3.2. 绑定输出

* **响应Body:** response.content
* **Cookie：** response.cookies
* **请求Header：**response.headers

