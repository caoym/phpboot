# ezsql
An an easy-to-use SQL builder.

## HOW TO USE

    $db = new \PDO($dsn, $username, $passwd);
    
### SELECT

    $res = Sql::select('a, b')
       ->from('table')
       ->leftJoin('table1')->on('table.id=table1.id')
       ->where('a=?',1)
       ->groupBy('b')->having('sum(b)=?', 2)
       ->orderBy('c', Sql::$ORDER_BY_ASC)
       ->limit(0,1)
       ->forUpdate()->of('d')
       ->get($db);
### UPDATE
    
    $rows = Sql::update('table')
       ->set('a', 1)
       ->where('b=?', 2)
       ->orderBy('c', Sql::$ORDER_BY_ASC)
       ->limit(1)
       ->exec($db)
       ->rows
       
### INSERT

    $newId = Sql::insertInto('table')
       ->values(['a'=>1])
       ->exec($db)
       ->lastInsertId()
       
### DELETE
   
    $rows = Sql::deleteFrom('table')
       ->where('b=?', 2)
       ->orderBy('c', Sql::$ORDER_BY_ASC)
       ->limit(1)
       ->exec($db)
       ->rows

