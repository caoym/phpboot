# Annotation

PhpBoot 框架较多的使用了 Annotation。当然原生 PHP 语言并不支持此项特性，所以实际是通过Reflection提取注释并解析实现，类似很多主流 PHP 框架的做法（如 symfony、doctrine 等）。但又有所不同的是，主流的Annotation 语法基本沿用了 java 中的形式，如：

```php
/**
 * @Route("/books/{id}", name="book_info")
 * @Method("GET")
 */
 public function getBook($id)...
```
语法严谨，易于扩展，但稍显啰嗦(PhpBoot 1.x 版本也使用此语法)。特别是PHP 由于先天不足（原生不支持Annotation），通过注释，在没有IDE语法提示和运行时检查机制的情况下。如果写 Annotation 过于复杂，那还不然直接写原生代码。所以 PhpBoot 使用了更简单的 Annotation 语法。如：

```php
/**
 * @route GET /books/{id}
 */
 public function getBook($id)...

```

## 1. 语法

```@<name> [param0] [param1] [param2] ...```

1. name 只能是连续的字母、数字、斜杠'\'、中横杠'-' 组成的字符串，建议全小写，单词间用'-'分割，如```@myapp\my-ann```。
2. name和参数，参数和参数见，用空白符（一个或多个连续的空格、制表符）分割。
3. 参数中如果包含空格，应将参数用双引号""包围，包围内的双引号用\转义，如 ```@my-ann "the param \"0\"" param1``` 第一个参数为```the param "0"```

**分割参数、转义的语法和linux 命令行的语法类似**

## 2. 嵌套

嵌套注释，用{}包围， 比如```@param int size {@v min:0|max:10}```






