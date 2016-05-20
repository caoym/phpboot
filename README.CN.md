# phprs
这是一个轻量级框架，专为快速开发RESTful接口而设计。如果你和我一样，厌倦了使用传统的MVC框架编写微服务或者前后端分离的API接口，受不了为了一个简单接口而做的很多多余的coding（和CTRL-C/CTRL-V），那么，你肯定会喜欢这个框架！


要求:PHP5.4+

[![Build Status](https://travis-ci.org/caoym/phprs-restful.svg)](https://travis-ci.org/caoym/phprs-restful)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/caoym/phprs-restful/master/LICENSE)
## 先举个栗子 
1. 写个HelloWorld.php，放到框架指定的目录下（默认是和index.php同级的apis/目录）

    ```PHP
    /**
     * @path("/hw")
     */
    class HelloWorld
    {
        /** 
         * @route({"GET","/"})
         */
        public function doSomething() {
            return "Hello World!";
        }
    }
    ```
2. 浏览器输入http://your-domain/hw/
    你将看到：`Hello World!`就是这么简单，不需要额外配置，不需要继承也不需要组合。

## 发生了什么
回过头看HelloWorld.php，特殊的地方在于注释（@path，@route），没错，框架**通过注释获取路由信息和绑定输入输出**。但不要担心性能，注释只会在类文件修改后解析一次。更多的@注释后面会说明。

## 再看个更具体的例子
这是一个登录接口
    
```PHP
/**
 * 用户权限验证
 * @path("/tokens/") 
 */
class Tokens
{ 
    /**
     * 登录
     * 通过用户名密码授权
     * @route({"POST","/accounts/"}) 
     * @param({"account", "$._POST.account"}) 账号
     * @param({"password", "$._POST.password"}) 密码
     * 
     * @throws ({"InvalidPassword", "res", "403 Forbidden", {"error":"InvalidPassword"} }) 用户名或密码无效
     * 
     * @return({"body"})    
     * 返回token，同cookie中的token相同,
     * {"token":"xxx", "uid" = "xxx"}
     *
     * @return({"cookie","token","$token","+365 days","/"})  通过cookie返回token
     * @return({"cookie","uid","$uid","+365 days","/"})  通过cookie返回uid
     */
    public function createTokenByAccounts($account, $password, &$token,&$uid){
        //验证用户
        $uid = $this->users->verifyPassword($account, $password);
        Verify::isTrue($uid, new InvalidPassword($account));
        $token = ...;
        return ['token'=>$token, 'uid'=>$uid];
    } 
    /**
     * @property({"default":"@Users"})   依赖的属性，由框架注入。
     * 指定框架在实例化Tokens时，会通过IoCFactory创建Users对象，并赋值给$users。
     *
     * @var Users
     */
    public $users;
}
```
## 安装部署
配置webserver，指定所有restful请求rewrite到index.php, 如

- Nginx
```
location / {
  rewrite ^(/.*)?$ /index.php$1 break;
}
```
- Apache
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond $1 !^(index\.php)
RewriteRule ^(.*)$ /index.php/$1 [L]
```


## 还能做什么
1. 依赖管理（依赖注入），
2. 自动输出接口文档（不是doxgen式的类、方法文档，而是描述http接口的文档）
3. 接口缓存
4. hook

## 为什么又多一个框架
开源PHP框架已经很多，仅仅是适合开发Restful的PHP框架就不乏优秀的，如laravel、anandkunal/ToroPHP、peej/tonic等等，那么为何还有phprs呢？自己总结一下主要有几个原因：

1. **总是一起做的事情，就要放在一起做**：比如通常实现一个新功能，需要：定义接口、实现代码、配置路由、写接口文档，而大部分框架需要程序猿们切换到不同地方（场景）去做这些事情；反过来，不相关的，却又通常耦合在一起，比如路由定义。

2. **低侵入性**：大部分框架都过多的干涉了上层的代码，比如需要通过继承使用框架的功能，需要使用框架的设计模式比如MVC。虽然通常这些限制是有益的，特别是对于大型项目。但这些限制并不一定要框架提供，对于“轻+简单”为优势的框架，我更希望它使用起来像”没有框架“。

3. **简单**：这一点其实与上面两点重复，但重要的事情要说三遍。

## 社区
欢迎加入 QQ群 185193529

## 手册

请移步[wiki](https://github.com/caoym/phprs-restful/wiki/%E4%B8%AD%E6%96%87)
