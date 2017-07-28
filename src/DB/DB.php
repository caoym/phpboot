<?php
namespace PhpBoot\DB;

use PhpBoot\DB\rules\select\SelectRule;
use PhpBoot\DB\rules\insert\InsertRule;
use PhpBoot\DB\rules\update\UpdateRule;
use PhpBoot\DB\rules\delete\DeleteRule;
use PhpBoot\DB\rules\replace\ReplaceIntoRule;

require_once __DIR__.'/rules/select.php';
require_once __DIR__.'/rules/insert.php';
require_once __DIR__.'/rules/update.php';
require_once __DIR__.'/rules/delete.php';
require_once __DIR__.'/rules/replace.php';

/**
 * 
 * How-to-use:
 * 
 * $db = new DB(...);
 * // 1. select
 * $res = $db->select('a, b')
 *      ->from('table')
 *      ->leftJoin('table1')->on('table.id=table1.id')
 *      ->where('a=?',1)
 *      ->groupBy('b')->having('sum(b)=?', 2)
 *      ->orderBy('c', Sql::ORDER_BY_ASC)
 *      ->limit(0,1)
 *      ->forUpdate()->of('d')
 *      ->get();
 * 
 * // 2. update
 * $rows = $db->update('table')
 *      ->set('a', 1)
 *      ->where('b=?', 2)
 *      ->orderBy('c', Sql::ORDER_BY_ASC)
 *      ->limit(1)
 *      ->exec($db)
 *      ->rows
 *      
 * // 3. insert
 * $newId = $db->insertInto('table')
 *      ->values(['a'=>1])
 *      ->exec($db)
 *      ->lastInsertId()
 *      
 * //4. delete
 * $rows = $db->deleteFrom('table')
 *      ->where('b=?', 2)
 *      ->orderBy('c', Sql::ORDER_BY_ASC)
 *      ->limit(1)
 *      ->exec($db)
 *      ->rows
 *      
 * @author caoym <caoyangmin@gmail.com>
 */
class DB{

    /**
     * DB constructor.
     * @param string $dsn @see \PDO
     * @param string $username @see \PDO
     * @param string $password @see \PDO
     * @param array $options @see \PDO
     */

    static public function connect($dsn,
                                  $username,
                                  $password,
                                  $options = [])
    {
        $options += [
            \PDO::ATTR_ERRMODE =>\PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'utf8';",
            \PDO::MYSQL_ATTR_FOUND_ROWS => true
        ];

        $connection = new \PDO($dsn, $username, $password, $options);
        return new DB($connection);
    }

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * select('column0,column1') => "SELECT column0,column1"
     *   
     * select('column0', 'column1') => "SELECT column0,column1"
     * 
     * @param string $column0
     * @return \PhpBoot\DB\rules\select\FromRule
     */
    function select($column0=null, $_=null){
        $obj = new SelectRule(new Context($this->connection));
        if($column0 == null){
            $args = ['*'];
        }elseif(is_array($column0)){
            $args = $column0;
        }else{
            $args = func_get_args();
        }
        foreach ($args as &$arg){
            $arg = trim($arg);
            if($arg == '*'){
                continue;
            }
            $found = [];
            if(preg_match('/^[A-Za-z0-9]+$/', $arg, $found, PREG_OFFSET_CAPTURE)&&
                count($found)){
                $arg = "`$arg`";
            }
        }
        return $obj->select(implode(',', $args));
    }
    /** 
     * insertInto('table') => "INSERT INTO table"
     * 
     * @param string $table
     * @return \PhpBoot\DB\rules\insert\ValuesRule
     */
    public function insertInto($table) {
        $obj = new InsertRule(new Context($this->connection));
        return $obj->insertInto($table);
    }
    /**
     * update('table') => "UPDATE table"
     * @param string $table
     * @return \PhpBoot\DB\rules\update\UpdateSetRule
     */
    public function update($table) {
        $obj = new UpdateRule(new Context($this->connection));
        return $obj->update($table);
    }
    
    /**
     * deleteFrom('table') => "DELETE FROM table"
     * @param string $table
     * @return \PhpBoot\DB\rules\basic\WhereRule
     */
    public function deleteFrom($table){
        $obj  =  new DeleteRule(new Context($this->connection));
        return $obj->deleteFrom($table);
    }
    /**
     * replaceInto('table') => "REPLACE INTO table"
     * @param string $table
     * @return \PhpBoot\DB\rules\replace\ValuesRule
     */
    public function replaceInto($table){
        $obj  =  new ReplaceIntoRule(new Context($this->connection));
        return $obj->replaceInto($table);
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    /**
	 * Splice sql use raw string(without escaping)
     * for example:
     * where('time>?', 'now()') => " WHERE time > 'now()' "
     * where('time>?', Sql::raw('now()')) => " WHERE time > now() "
     * @param string $str
     * @return Raw
     */
    static public function raw($str){
        return new Raw($str);
    }
    
    const ORDER_BY_ASC ='ASC';
    const ORDER_BY_DESC ='DESC';

    /**
     * @var \PDO
     */
    private $connection;
}
