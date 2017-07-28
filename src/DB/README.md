# ezsql
An an easy-to-use SQL builder.

## HOW TO USE

    $db = DB::connect($dsn, $username, $passwd);
    
### SELECT

    $res = $db->select('a, b')
       ->from('table')
       ->leftJoin('table1')->on('table.id=table1.id')
       ->where('a=?',1)
       ->groupBy('b')->having('sum(b)=?', 2)
       ->orderBy('c', Sql::ORDER_BY_ASC)
       ->limit(0,1)
       ->forUpdate()->of('d')
       ->get();
### UPDATE
    
    $rows = $db->update('table')
       ->set('a', 1)
       ->where('b=?', 2)
       ->orderBy('c', Sql::ORDER_BY_ASC)
       ->limit(1)
       ->exec()
       ->rows
       
### INSERT

    $newId = $db->insertInto('table')
       ->values(['a'=>1])
       ->exec()
       ->lastInsertId()
       
### DELETE
   
    $rows = $db->deleteFrom('table')
       ->where('b=?', 2)
       ->orderBy('c', Sql::ORDER_BY_ASC)
       ->limit(1)
       ->exec()
       ->rows

