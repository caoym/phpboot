# FAQ

## 是否必须使用 APC 扩展

PhpBoot 框架为提高性能, 会将路由及Annotation 分析后的其他元信息进行缓存。生产环境建议使用 APC 扩展, 开发环境可以用文件缓存代替 apc, 方法是在 config.php 里加一个配置

```php
Cache::class => \DI\object(FilesystemCache::class)
    ->constructorParameter('directory', sys_get_temp_dir())
```

## composer 更新失败怎么办

packagist.org 国内访问不稳定，可以翻墙试试，或者用国内的镜像[phpcomposer](phpcomposer.com), 执行下面命令

```
composer config repo.packagist composer https://packagist.phpcomposer.com
```

