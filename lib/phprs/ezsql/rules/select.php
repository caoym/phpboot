<?php
/**
 * $Id: select.php 246 2015-10-21 04:48:09Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace phprs\ezsql\rules\select;
use phprs\ezsql\rules\basic\BasicRule;
use phprs\ezsql\impls\ExecImpl;
use phprs\ezsql\impls\SelectImpl;
use phprs\ezsql\impls\FromImpl;
use phprs\ezsql\impls\JoinImpl;
use phprs\ezsql\impls\JoinOnImpl;
use phprs\ezsql\impls\WhereImpl;
use phprs\ezsql\impls\GroupByImpl;
use phprs\ezsql\impls\OrderByImpl;
use phprs\ezsql\impls\LimitImpl;
use phprs\ezsql\impls\ForUpdateOfImpl;
use phprs\ezsql\impls\ForUpdateImpl;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class SelectRule extends BasicRule
{
    /**
     * select('column0, column1') => "SELECT column0, column1"
     * select('column0', 'column1') => "SELECT column0, column1"
     * @param string $columns
     * @return \phprs\ezsql\rules\select\FromRule
     */
    public function select($columns) {
        SelectImpl::select($this->context, $columns);
        return new FromRule($this->context);
    }
}

class GetRule extends BasicRule
{
    /**
     * Execute sql and get responses 
     * @param \PDO $db
     * @param $errExce whether throw exceptions
     * @return array
     */
    public function get($db, $asDict=false,$errExce=true) {
        return ExecImpl::get($this->context, $db, $asDict,$errExce);
    }
}
class FromRule extends GetRule
{
    /**
     * from('table') => "FROM table"
     * @param string $table
     * @return \phprs\ezsql\rules\select\JoinRule
     */
    public function from($table){
        FromImpl::from($this->context, $table);
        return new JoinRule($this->context);
    }
}
class ForUpdateOfRule extends GetRule
{
    /**
     * forUpdate()->of('column') => 'FOR UPDATE OF column'
     * @param string $column
     * @return \phprs\ezsql\rules\select\GetRule
     */
    public function of($column){
        ForUpdateOfImpl::of($this->context, $column);
        return new GetRule($this->context);
    }
}
class ForUpdateRule extends GetRule
{
    /**
     * forUpdate() => 'FOR UPDATE'
     * @return \phprs\ezsql\rules\select\ForUpdateOfRule
     */
    public function forUpdate(){
        ForUpdateImpl::forUpdate($this->context);
        return new ForUpdateOfRule($this->context);
    }
}

class LimitRule extends ForUpdateRule
{
    /**
     * limit(0,1) => "LIMIT 0,1"
     * @param int $start
     * @param int $size
     * @return \phprs\ezsql\rules\select\ForUpdateRule 
     */
    public function limit($start, $size) {
        LimitImpl::limitWithOffset($this->context, $start, $size);
        return new ForUpdateRule($this->context);
    }
}

class OrderByRule extends LimitRule
{
    public function __construct($context){
        parent::__construct($context);
        $this->order = new OrderByImpl();
    }
    /**
     * orderBy('column') => "ORDER BY column"
     * orderBy('column', Sql::$ORDER_BY_ASC) => "ORDER BY column ASC"
     * orderBy('column0')->orderBy('column1') => "ORDER BY column0, column1"
     * 
     * @param string $column
     * @param string $order Sql::$ORDER_BY_ASC or Sql::$ORDER_BY_DESC
     * @return \phprs\ezsql\rules\select\OrderByRule
     */
    public function orderBy($column, $order=null) {
        $this->order->orderBy($this->context, $column, $order);
        return $this;
    }
    /**
     * orderByArgs(['column0', 'column1'=>Sql::$ORDER_BY_ASC]) => "ORDER BY column0,column1 ASC"
     * @param array $args
     * @return \phprs\ezsql\rules\select\OrderByRule
     */
    public function orderByArgs($args) {
        $this->order->orderByArgs($this->context, $args);
        return $this;
    }
    /**
     * @var OrderByImpl
     */
    private $order;
}

class HavingRule extends OrderByRule
{
    /**
     * 
     * having('SUM(a)=?', 1) => "HAVING SUM(a)=1"
     * having('a>?', Sql::native('now()')) => "HAVING a>now()"
     * having('a IN (?)',  [1, 2]) => "HAVING a IN (1,2)"
     * 
     * @param string $expr
     * @param string $_
     * @return \phprs\ezsql\rules\select\OrderByRule
     */
    public function having($expr, $_=null) {
        WhereImpl::having($this->context, $expr, array_slice(func_get_args(), 1));
        return new OrderByRule($this->context);
    }
    /**
     * 
     * havingArgs([
     *      'a'=>1, 
     *      'b'=>['IN'=>[1,2]]
     *      'c'=>['BETWEEN'=>[1,2]]
     *      'd'=>['<>'=>1]
     *      ])
     *      
     *      =>
     *      "HAVING a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1"
     *      
     *      
     * @param array $args
     * @return \phprs\ezsql\rules\select\OrderByRule
     */
    public function havingArgs($args) {
        WhereImpl::havingArgs($this->context, $args);
        return new OrderByRule($this->context);
    }
}
class GroupByRule extends OrderByRule
{
    /**
     * groupBy('column') => "GROUP BY column"
     * @param string $column
     * @return \phprs\ezsql\rules\select\HavingRule
     */
    public function groupBy($column) {
        GroupByImpl::groupBy($this->context, $column);
        return new HavingRule($this->context);
    }
}
class WhereRule extends GroupByRule
{
    /**
     *
     * where('a=?', 1) => "WHERE a=1"
     * where('a=?', Sql::native('now()')) => "WHERE a=now()"
     * where('a IN (?)',  [1, 2]) => "WHERE a IN (1,2)"
     *
     * @param string $expr
     * @param mixed $_
     * @return \phprs\ezsql\rules\select\GroupByRule
     */
    public function where($expr, $_=null) {
        WhereImpl::where($this->context, $expr, array_slice(func_get_args(), 1));
        return new GroupByRule($this->context);
    }
    /**
     * whereArgs([
     *      'a'=>1, 
     *      'b'=>['IN'=>[1,2]]
     *      'c'=>['BETWEEN'=>[1,2]]
     *      'd'=>['<>'=>1]
     *      ])
     *      
     *      =>
     *      "WHERE a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1"
     * @param array $args  
     * @return\phprs\ezsql\rules\select\GroupByRule
     */
    public function whereArgs($args) {
        WhereImpl::whereArgs($this->context,$args);
        return new GroupByRule($this->context);
    }
}

class JoinRule extends WhereRule
{
    /**
     * join('table1')->on('table0.id=table1.id') => "JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \phprs\ezsql\rules\select\JoinOnRule
     */
    public function join($table){
        JoinImpl::join($this->context,null, $table);
        return new JoinOnRule($this->context);
    }
    /**
     * leftJoin('table1')->on('table0.id=table1.id') => "LEFT JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \phprs\ezsql\rules\select\JoinOnRule
     */
    public function leftJoin($table){
        JoinImpl::join($this->context,'LEFT', $table);
        return new JoinOnRule($this->context);
    }
    /**
     * rightJoin('table1')->on('table0.id=table1.id') => "RIGHT JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \phprs\ezsql\rules\select\JoinOnRule
     */
    public function rightJoin($table) {
        JoinImpl::join($this->context,'RIGHT', $table);
        return new JoinOnRule($this->context);
    }
    /**
     * innerJoin('table1')->on('table0.id=table1.id') => "INNER JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \phprs\ezsql\rules\select\JoinOnRule
     */
    public function innerJoin($table) {
        JoinImpl::join($this->context,'INNER', $table);
        return new JoinOnRule($this->context);
    }
}

class JoinOnRule extends BasicRule
{
    /**
     * join('table1')->on('table0.id=table1.id') => "JOIN table1 ON table0.id=table1.id"
     * @param string $condition
     * @return \phprs\ezsql\rules\select\JoinRule
     */
    public function on($condition){
        JoinOnImpl::on($this->context, $condition);
        return new JoinRule($this->context);
    }
}



