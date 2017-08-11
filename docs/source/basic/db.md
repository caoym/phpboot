# 数据库

## 1. 使用

### 1.1. 配置

在 App 的配置中指定配置，如：

```
return [
    'DB.connection'=> 'mysql:dbname=testdb;host=127.0.0.1',
    'DB.username'=> 'dbuser',
    'DB.password'=> 'dbpassword',
    'DB.options' => []
];
```

### 1.2. 创建数据库连接实例

可通过依赖注入，获取数据库连接实例。如:

```
use PhpBoot\DB\DB;

class Books
{
    public function __construct(DB $db)
    {
        $this->db = $db;
    }
    ...
}
```

### 1.3. 多个数据库

有的时候，应用可能需要连接多个数据库。下面将展示如果给 Books 类单独指定数据库连接。

```
return [
    'Books.DB.connection'=> 'mysql:dbname=testdb;host=127.0.0.1',
    'Books.DB.username'=> 'dbuser',
    'Books.DB.password'=> 'dbpassword',
    'Books.DB.options' => []

    'Books.DB'=> \DI\factory([DB::class, 'connect'])
                ->parameter('dsn', \DI\get('Books.DB.connection'))
                ->parameter('username', \DI\get('Books.DB.username'))
                ->parameter('password', \DI\get('Books.DB.password'))
                ->parameter('options', \DI\get('Books.DB.options')),
    Books::class=>DI\object()
                ->constructorParameter('db', \DI\get('Books.DB')),       
];
```

## 2. 语法

### 2.1. SELECT

```PHP
$res = $db->select('a, b')
   ->from('table')
   ->leftJoin('table1')->on('table.id=table1.id')
   ->where('a=?',1)
   ->groupBy('b')->having('sum(b)=?', 2)
   ->orderBy('c', Sql::$ORDER_BY_ASC)
   ->limit(0,1)
   ->forUpdate()->of('d')
   ->get();
```

### 2.2. UPDATE

```PHP
$rows = $db->update('table')
   ->set('a', 1)
   ->where('b=?', 2)
   ->orderBy('c', Sql::$ORDER_BY_ASC)
   ->limit(1)
   ->exec()
   ->rows
```   
### 2.3. INSERT

```PHP
$newId = $db->insertInto('table')
   ->values(['a'=>1])
   ->exec()
   ->lastInsertId()
```   
    
### 2.4. DELETE

```PHP
$rows = $db->deleteFrom('table')
   ->where('b=?', 2)
   ->exec()
   ->rows
```

### 2.5. 存储过程

```PHP
$db->transaction(
	function(DB $db){
		$db->...;
	}
);
```
