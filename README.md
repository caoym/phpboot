# phprs 
Lightweight, easy-to-use and jax-rs like RESTful framework.

[中文文档](https://github.com/caoym/phprs-restful/blob/master/README.CN.md)
[![Build Status](https://travis-ci.org/caoym/phprs-restful.svg)](https://travis-ci.org/caoym/phprs-restful)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/caoym/phprs-restful/master/LICENSE)

# Requirements
PHP5.4+


## Features
1. IoC
2. Auto document
3. Cache
4. Hook
5. [More...](https://github.com/caoym/phprs-restful/wiki)

## Hello World
1. Put HelloWorld.php in your-project-dir/apis/

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
            return ['msg'=>'Hello World!'];
        }
    }
    ```
2. open http://your-domain/hw/

    ```JSON
    {
        "msg":"Hello World!"
    }
    ```
    
## What happened
See HelloWorld.php, the annotations like @path，@route are used to define routers. Phprs also use annotations for two-way parameter binding, dependency injection, etc.  

## Examples
A login api example
    
```PHP
/**
 * authentication
 * @path("/tokens/") 
 */
class Tokens
{ 
    /**
     * login
     * login with password
     * @route({"POST","/accounts/"}) 
     * @param({"account", "$._POST.account"}) user's account
     * @param({"password", "$._POST.password"}) user's password
     * 
     * @throws ({"InvalidPassword", "res", "403 Forbidden", {"error":"InvalidPassword"} }) invalid password or account
     * 
     * @return({"body"})    
     * return uid
     * {"uid" = "xxx"}
     *
     * @return({"cookie","token","$token","+365 days","/"})  return token with cookie
     * @return({"cookie","uid","$uid","+365 days","/"})  return uid  with cookie
     */
    public function createTokenByAccounts($account, $password, &$token,&$uid){
        $uid = $this->users->verifyPassword($account, $password);
        Verify::isTrue($uid, new InvalidPassword($account));
        $token = ...;
        return ['uid'=>$uid];
    } 
    /**
     * @property({"default":"@Users"})  inject an instantiation of 'Users'
     * that means $this->users will be initialized as 'Users' before __construct() called
     *
     * @var Users
     */
    public $users;
}
```

## Installation

1. Download and copy phprs-restful/lib/* --> your-project-dir/../lib/
2. Copy phprs-restful/example/index.php  --> your-project-dir/
3. Copy phprs-restful/example/conf.json  --> your-project-dir/
4. Mkdir your-project-dir/apis/ and puy your owner api files
5. Settiing webserver, route RESTful requests to index.php, for example:
    > Nginx
    
    ```
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    ```
    
    > Apache
    
    ```
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond $1 !^(index\.php)
    RewriteRule ^(.*)$ /index.php/$1 [L]
    ```
