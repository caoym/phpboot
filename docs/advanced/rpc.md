# RPC

RPC即远程过程调用，是一种常用的分布式系统间访问接口的方式。PhpBoot 提供强大又简单易用的 RPC 支持，可以让你像使用本地接口一样，方便的使用远程接口。

## 1. 示例

下面将通过实现一个订单服务的示例，演示 PhpBoot RPC 的使用。

### 1.1. 定义接口

为保持示例尽量简单，这里我们只实现“创建订单”这一个接口。
```php
/**
 * @path /orders
 */
interface OrderServiceInterface
{
    /**
     * @route POST /
     * @param ProductInfo $product 商品快照
     * @return string 返回订单号
     */
    public function createOrder(ProductInfo $product);
}
```

### 1.2. 实现接口

接口定义好以后， 我们需要在服务端，实现该服务接口，以便可以对外提供访问。

```php
/**
 * @path /orders
 */
class OrderService implements OrderServiceInterface 
{
    /**
     * @route POST /
     * @param ProductInfo $product 商品快照
     * @return string 返回订单号
     */
    public function createOrder(ProductInfo $product)
    {
        // create the order
        return $orderId;
    }
}
```

### 1.3. 远程调用接口

在客户端，可以通过下面方法调用远程的接口。

```php
$orderService =  $app->make(
    RpcProxy::class, 
    [
        'interface'=>OrderServiceInterface::class, 
        'prefix'=>'http://10.x.x.1/'
    ]
);
/**@var OrderServiceInterface $orderService*/

$orderId = $orderService->createOrder($product);
```

另一种推荐的方法是通过依赖注入创建代理类。如

```php
//配置依赖

return [
    OrderServiceInterface::class 
        => \DI\objet(RpcProxy::class)
            ->constructorParameter('interface', OrderServiceInterface::class)
            ->constructorParameter('prefix', 'http://10.x.x.1/')
    
]

```

```php
// 注入依赖

class AnotherService
{
    ...
    
    /**
     * @inject 
     * @var OrderServiceInterface
     */
    private $orderService;
    
    public function doSomething()
    {
        $orderId = $this->orderService->createOrder($product)
    }
}
```

## 2. 注意

**由于 RpcProxy 默认通过 __call 实现远程方法的调用，所以无法传递引用参数。当接口参数中存在引用参数时，应该针对接口实现一个RpcProxy的子类，并重写包含引用参数的方法。以下是示例**

```php
// 这是个典型的例子，接口的方法中有引用类型参数
/**
 * @path /orders
 */
interface OrderServiceInterface
{
    /**
     * @route GET /
     * @param int $offset
     * @param int $limit
     * @param int $total 此为引用类型参数， 用于返回查询的总条数
     * @return Order[] 返回订单列表
     */
    public function getOrders($offset, $limit, &$total);
}
```

```php
// 这是个典型的例子，接口的方法中有引用类型参数
/**
 * @path /orders
 */
class OrderServiceProxy extends RpcProxy implements OrderServiceInterface 
//如果不想实现OrderServiceInterface的所有方法，也可以不继承OrderServiceInterface
{
    /**
     * @route GET /
     * @param int $offset
     * @param int $limit
     * @param int $total 此为引用类型参数， 用于返回查询的总条数
     * @return Order[] 返回订单列表
     */
    public function getOrders($offset, $limit, &$total)
    {
        return $this->__call(__FUNCTION__, [$offset, $limit, &$total]);
    }
}
```

```php
//接下来可以通过OrderServiceProxy 访问远程接口了

$orderService =  $app->make(
    OrderServiceProxy::class, 
    [
        'interface'=>OrderServiceInterface::class, 
        'prefix'=>'http://10.x.x.1/'
    ]
);

$orderService->getOrders...

```

## 3. 并发访问

在使用远程服务时，有时可能需要同时访问多个远程接口。如果能并行执行，在一些情况下可以大大减少接口执行时间。PhpBoot RPC 提供了并发执行的功能。使用方法如下：

```php
$orderService = $app->make ...
$bookService = $app->make ...


$rpcRes = MultiRpc::run([
    function()use(orderService){
        return orderService->getOrders(...);
    },
    function(){
        return bookService->getBooks(...);

    }
])

$res = []
foreach($rpcRes as $i){
    list($success, $error) = $i
    if($error){
        //执行失败的原因
    }else{
        //执行成功， 处理$success
    }
}
return $res；

```
**注意，MultiRpc 内部是将需并发执行的操作，调用转换为递归调用，并在递归的最后，等待所有异步操作完成。 所以实际上，真正并发执行的只是网络请求，所有网络请求结束后，后续代码执行还是串行的**





