<?php
/**
 * $Id: basic.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace phprs\ezsql\rules\basic;

use phprs\ezsql\impls\ExecImpl;
use phprs\ezsql\impls\LimitImpl;
use phprs\ezsql\impls\OrderByImpl;
use phprs\ezsql\impls\WhereImpl;
require_once dirname(__DIR__).'/impls.php';

class BasicRule
{
    public function __construct($context){
        $this->context = $context;
    }
    public $context;
}

class ExecRule extends BasicRule
{
    /**
     * Execute sql
     * @param \PDO $db
     * @param boolean $errExce whether throw exceptios
     * @return Response
     */
    public function exec($db, $errExce=true) {
        return ExecImpl::exec($this->context, $db, $errExce);
    }
}

class LimitRule extends ExecRule
{
    /**
     * limit(1) => "LIMIT 1"
     * @param int $size
     * @return \phprs\ezsql\rules\basic\ExecRule
     */
    public function limit($size) {
        LimitImpl::limit($this->context, $size);
        return new ExecRule($this->context);
    }
}

class OrderByRule extends LimitRule
{
    public function __construct($context){
        parent::__construct($context);
        $this->impl = new OrderByImpl();
    }
    /**
     * orderByArgs(['column0', 'column1'=>Sql::$ORDER_BY_ASC]) => "ORDER BY column0,column1 ASC"
     * @param array $orders
     * @return \phprs\ezsql\rules\basic\LimitRule
     */
    public function orderByArgs($orders) {
        $this->impl->orderByArgs($this->context, $orders);
        return new LimitRule($this->context);
    }
    /**
     * 
     * orderBy('column') => "ORDER BY column"
     * orderBy('column', Sql::$ORDER_BY_ASC) => "ORDER BY column ASC"
     * orderBy('column0')->orderBy('column1') => "ORDER BY column0, column1"
     * 
     * @param string $column
     * @param string $order Sql::$ORDER_BY_ASC or Sql::$ORDER_BY_DESC
     * 
     * @return \phprs\ezsql\rules\basic\LimitRule
     */
    public function orderBy($column, $order=null) {
        $this->impl->orderBy($this->context, $column, $order);
        return new LimitRule($this->context);
    }
    private $impl;
}

class WhereRule extends OrderByRule
{
    /**
     * 
     * where('a=?', 1) => "WHERE a=1"
     * where('a=?', Sql::native('now()')) => "WHERE a=now()"
     * where('a IN (?)',  [1, 2]) => "WHERE a IN (1,2)"
     * 
     * @param string $expr
     * @param mixed $_
     * @return \phprs\ezsql\rules\basic\OrderByRule
     */
    public function where($expr, $_= null) {
        WhereImpl::where($this->context, $expr, array_slice(func_get_args(), 1));
        return new OrderByRule($this->context);
    }
    /**
     * 
     * whereArgs([
     *      'a'=>1, 
     *      'b'=>['IN'=>[1,2]]
     *      'c'=>['BETWEEN'=>[1,2]]
     *      'd'=>['<>'=>1]
     *      ])
     *      
     *      =>
     *      "WHERE a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1"
     * @param string $args
     * @return \phprs\ezsql\rules\basic\OrderByRule
     */
    public function whereArgs($args) {
        WhereImpl::whereArgs($this->context, $args);
        return new OrderByRule($this->context);
    }
}