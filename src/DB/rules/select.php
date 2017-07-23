<?php
namespace PhpBoot\DB\rules\select;
use PhpBoot\DB\rules\basic\BasicRule;
use PhpBoot\DB\impls\ExecImpl;
use PhpBoot\DB\impls\SelectImpl;
use PhpBoot\DB\impls\FromImpl;
use PhpBoot\DB\impls\JoinImpl;
use PhpBoot\DB\impls\JoinOnImpl;
use PhpBoot\DB\impls\WhereImpl;
use PhpBoot\DB\impls\GroupByImpl;
use PhpBoot\DB\impls\OrderByImpl;
use PhpBoot\DB\impls\LimitImpl;
use PhpBoot\DB\impls\ForUpdateOfImpl;
use PhpBoot\DB\impls\ForUpdateImpl;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class SelectRule extends BasicRule
{
    /**
     * select('column0, column1') => "SELECT column0, column1"
     * select('column0', 'column1') => "SELECT column0, column1"
     * @param string $columns
     * @return \PhpBoot\DB\rules\select\FromRule
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
     * @param string|false $asDict
     * @return array
     */
    public function get($asDict=false) {
        return ExecImpl::get($this->context, $asDict);
    }

    /**
     * Execute sql and get one response
     * @return false
     */
    public function getFirst(){
        $res = ExecImpl::get($this->context);
        if(count($res)){
            return $res[0];
        }
        return false;
    }
}
class FromRule extends GetRule
{
    /**
     * from('table') => "FROM table"
     * @param string $table
     * @return \PhpBoot\DB\rules\select\JoinRule
     */
    public function from($table,$as=null){
        FromImpl::from($this->context, $table,$as);
        return new JoinRule($this->context);
    }
}
class ForUpdateOfRule extends GetRule
{
    /**
     * forUpdate()->of('column') => 'FOR UPDATE OF column'
     * @param string $column
     * @return \PhpBoot\DB\rules\select\GetRule
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
     * @return \PhpBoot\DB\rules\select\ForUpdateOfRule
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
     * @return \PhpBoot\DB\rules\select\ForUpdateRule
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
     * orderBy('column', Sql::ORDER_BY_ASC) => "ORDER BY column ASC"
     * orderBy('column0')->orderBy('column1') => "ORDER BY column0, column1"
     * 
     * @param string $column
     * @param string $order Sql::ORDER_BY_ASC or Sql::ORDER_BY_DESC
     * @return \PhpBoot\DB\rules\select\OrderByRule
     */
    public function orderBy($column, $order=null) {
        $this->order->orderBy($this->context, $column, $order);
        return $this;
    }
    /**
     * orderByArgs(['column0', 'column1'=>Sql::ORDER_BY_ASC]) => "ORDER BY column0,column1 ASC"
     * @param array $args
     * @return \PhpBoot\DB\rules\select\OrderByRule
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
     * having('a>?', Sql::raw('now()')) => "HAVING a>now()"
     * having('a IN (?)',  [1, 2]) => "HAVING a IN (1,2)"
     * 
     * @param string $expr
     * @param string $_
     * @return \PhpBoot\DB\rules\select\OrderByRule
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
     * @return \PhpBoot\DB\rules\select\OrderByRule
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
     * @return \PhpBoot\DB\rules\select\HavingRule
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
     * where('a=?', Sql::raw('now()')) => "WHERE a=now()"
     * where('a IN (?)',  [1, 2]) => "WHERE a IN (1,2)"
     *
     * @param string $expr
     * @param mixed $_
     * @return \PhpBoot\DB\rules\select\GroupByRule
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
     * @return\PhpBoot\DB\rules\select\GroupByRule
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
     * @return \PhpBoot\DB\rules\select\JoinOnRule
     */
    public function join($table){
        JoinImpl::join($this->context,null, $table);
        return new JoinOnRule($this->context);
    }
    /**
     * leftJoin('table1')->on('table0.id=table1.id') => "LEFT JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \PhpBoot\DB\rules\select\JoinOnRule
     */
    public function leftJoin($table){
        JoinImpl::join($this->context,'LEFT', $table);
        return new JoinOnRule($this->context);
    }
    /**
     * rightJoin('table1')->on('table0.id=table1.id') => "RIGHT JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \PhpBoot\DB\rules\select\JoinOnRule
     */
    public function rightJoin($table) {
        JoinImpl::join($this->context,'RIGHT', $table);
        return new JoinOnRule($this->context);
    }
    /**
     * innerJoin('table1')->on('table0.id=table1.id') => "INNER JOIN table1 ON table0.id=table1.id"
     * @param string $table
     * @return \PhpBoot\DB\rules\select\JoinOnRule
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
     * @return \PhpBoot\DB\rules\select\JoinRule
     */
    public function on($condition){
        JoinOnImpl::on($this->context, $condition);
        return new JoinRule($this->context);
    }
}



