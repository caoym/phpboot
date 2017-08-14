# ORM

目前 PhpBoot 提供基本的 ORM 支持，包括：

## 1. 定义实体

实体对应数据库的表， 实体的属性名和数据库的列名一致。下面是一个典型的实体定义：

```php
/**
 * 图书信息
 * @table books
 * @pk id
 */
class Book
{
    /**
     * @var int
     */
    public $id;
    
    /**
     * 书名
     * @var string
     */
    public $name='';
    
    /**
     * 图片url
     * @var string[]
     */
    public $pictures=[];
}
```
其中:
* @table 指定表名
* @pk 指定表的主键
* @var 定义列的类型，如果类型为对象或者数组，则保存到数据库是将被序列化为 json

可以看到，ORM 中的实体和接口中的实体很类似，事实上，我们**鼓励在 ORM 和接口中复用实体类**。

## 2. 操作数据库

PhpBoot 提供量个组方法，```model``` 和 ```models```, 分别用于操作实体“实例”和实体“类”。

### 2.1. model 方法

```model()``` 方法用于操作实体“实例”，或者说操作单个实体对象。

#### 2.1.1 create

存储指定实体实例（对应 SQL 的 insert）

```php
$book = new Book();
$book->name = ...
...

\PhpBoot\model($this->db, $book)->create();
echo $book->id; //获取自增主键的值
```

#### 2.1.2 update

更新实体对应的数据库记录（对应 SQL 的 update）

```php
$book = new Book();
$book->id = ...
...

\PhpBoot\model($this->db, $book)->update();
```

#### 2.1.3 delete

删除实体对应的数据库记录（对应 SQL 的 delete ）

```php
$book = new Book();
$book->id = ...

\PhpBoot\model($this->db, book)->delete();
```

### 2.2 models 方法

```models()``` 方法用于操作实体“类”，或者说操作一组实体。

#### 2.2.1. find

根据主键查找（对应 SQL 的 select ）

```php
$book = \PhpBoot\models($this->db, Book::class)->find($id);
```

#### 2.2.2. findWhere

根据组合查询条件查找（对应 SQL 的 select ）

```php
$books = \PhpBoot\models($this->db, Book::class)
    ->findWhere(['name'=>'abc'])
    ->get();
```

#### 2.2.3. update

根据主键更新（对应 SQL 的 update ）

```php
\PhpBoot\models($this->db, Book::class)
    ->update(1， ['name'=>'abc']);
```

#### 2.2.4. updateWhere

根据组合查询条件更新（对应 SQL 的 update ）

```php
\PhpBoot\models($this->db, Book::class)
    ->updateWhere(['name'=>'abc'], ['id'=>1])
    ->exec();
```

#### 2.2.6. delete

根据主键删除（对应 SQL 的 delete ）

```php
\PhpBoot\models($this->db, Book::class)
    ->delete(1);
```

#### 2.2.7. deleteWhere

根据组合查询条件删除（对应 SQL 的 delete ）

```php
\PhpBoot\models($this->db, Book::class)
    ->deleteWhere(['id'=>1])
    ->exec();
```
