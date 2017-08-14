# 文档输出

## 1. Swagger 文档

Swagger 是流行的 HTTP API 描述规范，同时 Swagger 官方还提供了丰富的工具，比如用于文档展示和接口测试的 Swagger UI， 相关资料请阅读[官方文档](https://swagger.io)。

以 phpboot-example 为例，生成的文档如下。文档中除了描述了接口的路由、参数定义、参数校验，还提供了接口测试工具。[点击这里查看在线 Demo](http://118.190.86.50:8007/index.html?url=http://118.190.86.50:8009/docs/swagger.json)

![](/_static/WX20170809-184015.png)

**PhpBoot 项目可以很方便的生成 Swagger 文档，无需添加额外的 Annotation**（很多框架为支持 Swagger，通常需要增加很多额外的注释，而这些注释只用于 Swagger。PhpBoot 生成 Swagger 的信息来自路由的标准注释，包括@route, @param, @return，@throws 等）

如需开启 Swagger 文档，只需在在 Application 初始化时 添加以下代码：

```php
PhpBoot\Docgen\Swagger\SwaggerProvider::register($app , function(Swagger $swagger){
    $swagger->host = 'example.com';
    $swagger->info->description = 'this is the description of the apis';
    ...
});
```

然后访问你的项目 url+/docs/swagger.json```如( http://localhost/docs/swagger.json)```，即可获取 json 格式的 Swagger 文档。

## 2. MarkDown 文档

开发中...