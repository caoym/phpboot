# 依赖注入
PhpBoot 使用开源项目 [PHP-DI](http://php-di.org/) 作为依赖注入的基础实现。

## 1. 手动注入
手动注入是只通过配置文件显式的指定注入方式。详见[PHP-DI文档](http://php-di.org/) 

## 2. 自动注入


### 2.1. 构造函数注入

```php
class Books
{
    /**
     * @param LoggerInterface $logger 通过依赖注入传入
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger;
    }
    ...
}
```



### 2.2. 属性注入

```php
class Books
{
    use EnableDIAnnotations; //启用通过@inject标记注入依赖
    /**
     * @inject 
     * @var DB
     */
    private $db;
}
```

**注意：PhpBoot 禁用了PHP-DI的 Annotation 注入方式，@inject 方式是 PhpBoot 实现的**

