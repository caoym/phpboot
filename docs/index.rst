PhpBoot
========

`PhpBoot <https://github.com/caoym/phpboot>`_ 是为快速开发 **微服务** / **RESTful API** 设计的PHP框架。它可以帮助开发者更聚焦在业务本身, 而将原来开发中不得不做, 但又重复枯燥的事情丢给框架, 比如编写接口文档、参数校验和远程调用代码等。

特色
----------

PhpBoot 框架提供许多主流的特性, 如IOC、AOP、ORM等。 这些特性都经过精心设计和选择(有些是第三方开源代码,如 PHP-DI)，但和其他框架相比较, PhpBoot 最显著的特色是:

**1. 以面向对象的方式编写接口**

你肯定看到过这样的代码:

.. code-block:: php

    // **不用** PhpBoot 的代码
    class BookController
    {
        public function findBooks(Request $request)
        {
            $name = $request->get('name');
            $offset = $request->get('offset', 0);
            $limit = $request->get('limit', 10);
            ...
            return new Response(['total'=>$total, 'data'=>$books]);
        }

    public function createBook(Request $request)
        ...
    }


很多主流框架都需要用类似代码编写接口。但这种代码的一个问题是, 方法的输入输出隐藏在实现里, 这不是通常我们提倡的编码方式。如果你对代码要求更高, 你可能还会实现一层 Service 接口, 而在 Controller 里只是简单的去调用 Service 接口。而使用 PhpBoot, 你可以用更自然的方式去定义和实现接口。上面的例子, 在 PhpBoot 框架中实现是这样的:

.. code-block:: php

    /**
     * @path /books/
     */
    class Books
    {
        /**
         * @route GET /
         * @return Book[]
         */
        public function findBooks($name, &$total=null, $offset=0, $limit=10)
        {
            $total = ...
            ...
            return $books;
        }

        /**
         * @route POST /
         * @param Book $book {@bind request.request} bind $book with http body
         * @return string id of created book
         */
        public function createBook(Book $book)
        {
            $id = ...
            return $id;
        }
    }

上面两份代码执行的效果是一样的。可以看到 PhpBoot 编写的代码更符合面向对象编程的原则, 以上代码完整版本请见 `phpboot-example <https://github.com/caoym/phpboot-example>`_ 。
    
**2. 轻松支持 Swagger**

`Swagger <https://swagger.io>`_ 是目前最流行的接口文档框架。虽然很多框架都可以通过扩展支持Swagger, 但一般不是需要编写很多额外的注释, 就是只能导出基本的路由信息, 而不能导出详细的输入输出参数。而 PhpBoot 可以在不增加额外编码负担的情况下, 轻松去完成上述任务，下图为findBooks对应的文档。更多内容请见 `文档 <http://phpboot.org/zh/latest/advanced/docgen.html>`_ 和 `在线 Demo <http://swagger.phpboot.org/?url=http%3a%2f%2fexample.phpboot.org%2fdocs%2fswagger.json>`_ 。

.. image:: https://github.com/caoym/phpboot/raw/master/docs/_static/WX20170809-184015.png

**3. 简单易用的分布式支持**

使用 PhpBoot 可以很简单的构建分布式应用。通过如下代码, 即可轻松远程访问上面示例中的 Books 接口:

.. code-block:: php

    $books = $app->make(RpcProxy::class, [
            'interface'=>Books::class,
            'prefix'=>'http://x.x.x.x/'
        ]);

    $books->findBooks(...);


同时还可以方便的发起并发请求, 如:

.. code-block:: php

    $res = MultiRpc::run([
        function()use($service1){
            return $service1->doSomething();
        },
        function()use($service2){
            return $service2->doSomething();
        },
    ]);


更多内容请查看 `文档 <http://phpboot.org/zh/latest/advanced/rpc.html>`_

**4. IDE 友好**

IDE 的代码提示功能可以让开发者轻松不少, 但很多框架在这方面做的并不好, 你必须看文档或者代码, 才能知道某个功能的用法。PhpBoot 在一开始就非常注重框架的 IDE 友好性, 尽可能让框架保持准确的代码提示。比如下图是 DB 库在 PhpStorm 下的使用:

.. image:: https://github.com/caoym/phpboot/raw/master/docs/_static/db.gif


可以看到, IDE 的提示是符合 SQL 语法规则的, 并不是简单罗列所有 SQL 指令。

帮助
------
* *QQ 交流群:185193529*
* *本人邮箱 caoyangmin@gmail.com*
  
文档
------
  
.. toctree::
    :maxdepth: 1
    :caption: 快速开始

    quick-start/install.md
    quick-start/requirements.md
    quick-start/webserver-config.md
    quick-start/example.md

.. toctree::
    :maxdepth: 1
    :caption: 基础特性

    basic/annotation
    basic/route.md
    basic/params-bind.md
    basic/validation.md
    basic/di.md
    basic/db.md

.. toctree::
    :maxdepth: 1
    :caption: 高级特性

    advanced/docgen.md
    advanced/orm.md
    advanced/hook.md
    advanced/rpc.md
    advanced/workflow.md

.. toctree::
    :maxdepth: 1
    :caption: FAQ

    faq.md
