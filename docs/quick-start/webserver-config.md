# WebServer 配置

为了使用PhpBoot，你需要配置 WebServer，将所有动态请求指向 index.php

## 1. Nginx

若使用 Nginx ，修改你的项目对应的配置：

```
server {
    listen 80;
    server_name example.com;
    index index.php;
    error_log /path/to/example.error.log;
    access_log /path/to/example.access.log;
    root /path/to/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

## 2. Apache

Apache 的配置稍微复杂，首先你需要启 mod_rewrite 模块，然后在 index.php 目录下添加 .htaccess 文件：

```
Options +FollowSymLinks
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

另外还需要修改虚拟主机的AllowOverride配置

```
AllowOverride All
```

**注意：由于 WebServer 版本的差异， 以上配置可能不能按预期工作，但这是使用多数 PHP 框架第一步需要解决的问题， 网上有会有很多解决方案，用好搜索引擎即可**
