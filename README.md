# phprs
这是一个轻量级框架，专为快速开发RESTful接口而设计。如果你和我一样，厌倦了使用传统的MVC框架编写微服务或者前后端分离的API接口，受不了为了一个简单接口而做的很多多余的coding（和CTRL-C/CTRL-V），那么，你肯定会喜欢这个框架！


要求:PHP5.4+

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
     * @property({"default":"@Users"})   依赖的属性，由框架注入
     * @var Users
     */
    public $users;
}
```
## 还能做什么
1. 依赖管理（依赖注入），
2. 自动输出接口文档（不是doxgen式的类、方法文档，而是描述http接口的文档）
3. 接口缓存
4. hook

## 担心可用性？
此框架目前支持线上亿级PV。有问题或意见也欢迎联系我：caoyangmin@gmail.com

## 手册

请移步[wiki](https://github.com/caoym/phprs/wiki)
