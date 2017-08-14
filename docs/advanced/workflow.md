# 工作流

这将是一个简单、轻量、健壮、可扩展、适用于自动化为主的、支持主要的BPMN要素（活动、网关、事件）、可持久化、但不准备支持所有BPMN2特性, 的工作流引擎，目前还在 Workflow 分支上开发...

## 1. 将可以通过如下方式定义流程

```php
$engine = new ProcessEngine();
$process = new Process();
$builder = new ProcessBuilder($process);

//定义流程
$builder
    ->begin
        ->task(null, CreateOrderTask::class, '创建订单')
        ->eFork('eFork1', '事件网关')
            ->listener(null, 'paid'， '等待支付')
            ->task(null, ShipTask::cass, '发货')
        ->xJoin('xJoin1', '排他网关')
    ->end;
    
$builder 
        ->eFork1
            ->timer(null, 3600, '支付超时')
            ->task(null, CloseOrderTask::class, '关闭订单')
        ->xJoin1
   
    
//执行流程
$process->run($engine);

```
