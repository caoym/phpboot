# 缓存

PhpBoot 使用[doctrine/cache](http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html)作为底层缓存实现。doctrine/cache 支持的缓存类型有: APC、APCu、Memcache、Xcache、Redis。

## 业务缓存 

如果需要在业务代码中使用缓存, 此处以Redis为例, 演示基本用法。

1. 在修改配置文件 config.php, 增加以下代码:

```php

'redis' => \DI\object(\Doctrine\Common\Cache\RedisCache::class)
    ->method('setRedis', \DI\factory(function(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
    })),
```

2. 在控制器中需要 redis 的地方, 注入 redis 实例

```php

/**
 * @inject redis
 * @var \Doctrine\Common\Cache\RedisCache
 */
private $redis;
```

## 系统缓存

PhpBoot 框架为提高性能, 会将路由及Annotation 分析后的其他元信息进行缓存。生产环境建议使用 APC 扩展, 开发环境可以用文件缓存代替 apc, 方法是在 config.php 里加一个配置。

```php
Cache::class => \DI\object(FilesystemCache::class)
    ->constructorParameter('directory', sys_get_temp_dir())
```
