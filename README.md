# phprs
这是一个轻量级框架，专为快速开发Restful接口而设计。如果你和我一样，厌倦了使用传统的MVC框架编写微服务或者前后端分离的API接口，受不了为了一个简单接口而做的很多多余的coding（和CTRL-C/CTRL-V），那么，你肯定会喜欢这个框架！

## 先举个例子 
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
2. 浏览器输入http://your-domain/hw/，你将看到：
    `Hello World!`**就是这么简单，不需要额外配置，不需要继承也不需要组合**。

## 发生了什么
回过头看HelloWorld.php，特殊的地方在于注释（@path，@route），没错，框架通过注释获取路由信息和绑定输入输出。但不要担心性能，注释只会在类文件修改后解析一次。更多的@注释后面会说明。


