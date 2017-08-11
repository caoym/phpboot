# 环境要求

PhpBoot 框架有一些系统上的需求：

* PHP 版本 >= 5.5.9
* APC 扩展启用

```
apc.enable=1
```

* 如果启用了OPcache，应同时配置以下选项：

```
opcache.save_comments=1
opcache.load_comments=1
```

