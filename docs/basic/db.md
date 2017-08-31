# 数据库

## 1. 配置
可以通过依赖注入的方式，对数据库进行配置。

1. 在需要数据库的类中加入依赖注入代码：
	
	```php
	use PhpBoot\DB\DB;
	use PhpBoot\DI\Traits\EnableDIAnnotations;
	
	class Books
	{
	    use EnableDIAnnotations; //启用通过@inject标记注入依赖
	
	    /**
	     * @inject
	     * @var DB
	     */
	    private $db;
	    
	    public function getBooks()...
	}
	```
	框架在实例化```Books```后，根据```@inject```注释, 自动给属性```$db```赋值，其逻辑等价于:
	
	```php
	$books->db = $app->get(DB::class);
	```
	
2. 修改数据库配置
	
	在 config.php 中加入以下配置（数据库地址等需根据实际情况修改）：
	
	```php
	'DB.connection'=> 'mysql:dbname=phpboot-example;host=127.0.0.1',
	'DB.username'=> 'root',
	'DB.password'=> 'root',
	'DB.options' => [],
	```
	
## 2. 编写 SQL

下面将通过实现 createBook、deleteBook、updateBook、findBooks 方法，演示insert、delete、update、select 的使用。

### 2.1 INSERT

```php
public function createBook(Book $book)
{
    $newId = $this->db->insertInto('books')
        ->values([
	        'name'=>$book->name,
	        'brief'=>$book->brief,
	        ...
        ])
        ->exec()
        ->lastInsertId(); 
    return $newId;
}
```

### 2.2 DELETE

```php
public function deleteBook($id)
{
    $this->db->deleteFrom('books')
        ->where(['id'=>$id])
        ->exec();
}
```

### 2.3 UPDATE

```php
public function updateBook(Book $book)
{
    $this->db->update('books')
        ->set([
	        'name'=>$book->name,
	        'brief'=>$book->brief,
	        ...
        ])
        ->where(['id'=>$book->id])
        ->exec(); 
}
```

### 2.4 SELECT

```php
public function findBooks($name, $offsit, $limit)
{
    $books = $this->db->select('*')
        ->from('books')
        ->where('name LIKE ?', "%$name%")
        ->orderBy('id')
        ->limit($offsit, $limit)->get();
        
    return $books;
}
```

## 3. 高级用法

上述示例展示了```PhpBoot\DB```的基础用法，```PhpBoot\DB```同时也支持更复杂的SQL。

### 3.1 复杂 WHERE

类似 SQL ```WHERE a=1 OR (b=2 and c=3)```, 可以以下代码实现:

```php
->where(['a'=>1])
->orWhere(function(ScopedQuery $query){
    $query->where(['b'=>2, 'c'=>3])
})
```

上面例子中，```ScopedQuery``` 中还能再嵌套 ```ScopedQuery```。

### 3.2 JOIN

```php
$db->select('books.*', DB::raw('authors.name as author'))
    ->from('books')
    ->where(['books.id'=>1])
    ->leftJoin('authors')->on('books.authorId = authors.id')
    ->get()
```

### 3.3 WHERE ... IN ...

使用```PDO```时，```WHERE IN```的预处理方式很不方便，需要为```IN```的元素预留数量相等的```?```, 比如：

```php
$pdo->prepare(
    'SELECT * FROM table WHERE a IN (?,?,?)'
)->execute([1,2,3])
```

而使用```PhpBoot\DB```可以解决这个问题：

```php
$db->select()->from('table')->where('a IN (?)', [1,2,3]);
```

### 3.4 使用 SQL 函数

默认情况下，框架会对输入做转换, 如会在表名和列名外加上``` `` ```，会把变量作为绑定处理，比如下面的语句

```php
$db->select('count(*) AS count')
    ->from('table')
    ->where(['time'=>['>'=>'now()']]);
```

 等价 的 SQL：

```sql
SELECT `count(*) AS count` FROM `table` where `time` > 'now()'
```

如果希望框架不做转换，需要使用```DB::raw()```,比如：

```php
$db->select(DB::raw('count(*) AS count'))
    ->from('table')
    ->where(['time'=>['>'=>DB::raw('now()')]]);
```

与下面 SQL 等价

```sql
SELECT count(*) AS count FROM `table` where `time` > now()
```


### 3.5 子查询

下面代码演示子查询用法：

```php
$parent = $db->select()->from('table1')->where('a=1');
$child = $db->select()->from($parent);
```

与下面 SQL 等价

```sql
SELECT * FROM (SELECT * FROM `table1` WHERE a=1)
```

### 3.6 事务

```php
$db->transaction(
    function(DB $db){
        $db->update('table1')->...
        $db->update('table1')->...
    }
)
```

事务允许嵌套，但只有最外层的事务起作用，内部嵌套的事务与最外层事务将被当做同一个事务。

## 4. 使用多个数据库

PhpBoot 为```DB``` 类定义了默认的构造方式，形式如下：

```php
DB::class => \DI\factory([DB::class, 'connect'])
    ->parameter('dsn', \DI\get('DB.connection'))
    ->parameter('username', \DI\get('DB.username'))
    ->parameter('password', \DI\get('DB.password'))
    ->parameter('options', \DI\get('DB.options')),
```

所以如果你的业务只使用连接一个数据库，只需要对```DB.connection, DB.username ,DB.password, DB.options```进行配置即可。但有的时候可能需要对在应用中连接不同的数据库，这时可以通过依赖注入配置多个库，如：

1. 先配置另一个数据库连接

	```php
	'another_db' => \DI\factory([DB::class, 'connect'])
	    ->parameter('dsn', 'mysql:dbname=phpboot-example;host=127.0.0.1')
	    ->parameter('username', 'root')
	    ->parameter('password', 'root')
	    ->parameter('options', [])
	```
	
2. 在需要的地方注入此连接

    ```php
    use PhpBoot\DB;
	
    class Books
    {
        use EnableDIAnnotations; //启用通过@inject标记注入依赖
        /**
         * @inject another_db
         * @var DB
        */
        private $db2;
    }
    ```
