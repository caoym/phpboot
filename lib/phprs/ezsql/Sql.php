<?php
/**
 * $Id: Sql.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace phprs\ezsql;

use phprs\ezsql\rules\select\SelectRule;
use phprs\ezsql\rules\insert\InsertRule;
use phprs\ezsql\rules\update\UpdateRule;
use phprs\ezsql\rules\delete\DeleteRule;
use phprs\ezsql\rules\basic\BasicRule;
use phprs\ezsql\rules\replace\ReplaceIntoRule;

require_once __DIR__.'/rules/select.php';
require_once __DIR__.'/rules/insert.php';
require_once __DIR__.'/rules/update.php';
require_once __DIR__.'/rules/delete.php';
require_once __DIR__.'/rules/replace.php';

/**
 * Easy SQL
 * 
 * How-to-use:
 * 
 * $db = new DB($dsn, $username, $passwd);
 * // 1. select
 * $res = Sql::select('a, b')
 *      ->from('table')
 *      ->leftJoin('table1')->on('table.id=table1.id')
 *      ->where('a=?',1)
 *      ->groupBy('b')->having('sum(b)=?', 2)
 *      ->orderBy('c', Sql::$ORDER_BY_ASC)
 *      ->limit(0,1)
 *      ->forUpdate()->of('d')
 *      ->get($db);
 * 
 * // 2. update
 * $rows = Sql::update('table')
 *      ->set('a', 1)
 *      ->where('b=?', 2)
 *      ->orderBy('c', Sql::$ORDER_BY_ASC)
 *      ->limit(1)
 *      ->exec($db)
 *      ->rows
 *      
 * // 3. insert
 * $newId = Sql::insertInto('table')
 *      ->values(['a'=>1])
 *      ->exec($db)
 *      ->lastInsertId()
 *      
 * //4. delete
 * $rows = Sql::deleteFrom('table')
 *      ->where('b=?', 2)
 *      ->orderBy('c', Sql::$ORDER_BY_ASC)
 *      ->limit(1)
 *      ->exec($db)
 *      ->rows
 *      
 * @author caoym <caoyangmin@gmail.com>
 */
class Sql{
    
    /**
     * select('column0,column1') => "SELECT column0,column1"
     *   
     * select('column0', 'column1') => "SELECT column0,column1"
     * 
     * @param $param0 columns
     * @return \phprs\ezsql\rules\select\FromRule
     */
    static public function select($param0='*', $_=null){
        $obj = new SelectRule(new SqlConetxt());
        $args = func_get_args();
        if(empty($args)){
            $args = ['*'];
        }
        return $obj->select(implode(',', $args));
    }
    /** 
     * insertInto('table') => "INSERT INTO table"
     * 
     * @param string $table
     * @return \phprs\ezsql\rules\insert\ValuesRule
     */
    static public function insertInto($table) {
        $obj = new InsertRule(new SqlConetxt());
        return $obj->insertInto($table);
    }
    /**
     * update('table') => "UPDATE table"
     * @param string $table
     * @return \phprs\ezsql\rules\update\UpdateSetRule
     */
    static public function update($table) {
        $obj = new UpdateRule(new SqlConetxt());
        return $obj->update($table);
    }
    
    /**
     * deleteFrom('table') => "DELETE FROM table"
     * @param string $table
     * @return \phprs\ezsql\rules\basic\WhereRule
     */
    static public function deleteFrom($table){
        $obj  =  new DeleteRule(new SqlConetxt());
        return $obj->deleteFrom($table);
    }
    /**
     * replaceInto('table') => "REPLACE INTO table"
     * @param string $table
     * @return \phprs\ezsql\rules\replace\ValuesRule
     */
    static public function replaceInto($table){
        $obj  =  new ReplaceIntoRule(new SqlConetxt());
        return $obj->replaceInto($table);
    }
    /**
	 * Splice sql use native string(without escaping)
     * for example:
     * where('time>?', 'now()') => " WHERE time > 'now()' "
     * where('time>?', Sql::native('now()')) => " WHERE time > now() "
     * @param string $str
     * @return Native
     */
    static public function native($str){
        return new Native($str);
    }
    
    static public $ORDER_BY_ASC ='ASC';
    static public $ORDER_BY_DESC ='DESC';
}
