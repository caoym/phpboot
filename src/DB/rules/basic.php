<?php
namespace PhpBoot\DB\rules\basic;

use PhpBoot\DB\Context;
use PhpBoot\DB\impls\ExecImpl;
use PhpBoot\DB\impls\LimitImpl;
use PhpBoot\DB\impls\OrderByImpl;
use PhpBoot\DB\impls\ExecResult;
use PhpBoot\DB\impls\WhereImpl;
require_once dirname(__DIR__).'/impls.php';

class BasicRule
{
    public function __construct(Context $context){
        $this->context = $context;
    }


    /**
     * @var Context
     */
    public $context;
}

class ExecRule extends BasicRule
{
    /**
     * Execute sql
     * @return ExecResult
     */
    public function exec() {
        return ExecImpl::exec($this->context);
    }
}

class LimitRule extends ExecRule
{
    /**
     * limit(1) => "LIMIT 1"
     * @param int $size
     * @return \PhpBoot\DB\rules\basic\ExecRule
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
     * 
     * orderBy('column') => "ORDER BY column"
     * orderBy('column', Sql::ORDER_BY_ASC) => "ORDER BY column ASC"
     * orderBy('column0')->orderBy('column1') => "ORDER BY column0, column1"
     * orderBy(['column0', 'column1'=>Sql::ORDER_BY_ASC]) => "ORDER BY column0,column1 ASC"
     * @param string $column
     * @param string $order Sql::ORDER_BY_ASC or Sql::ORDER_BY_DESC
     * 
     * @return \PhpBoot\DB\rules\basic\LimitRule
     */
    public function orderBy($column, $order=null) {
        $this->impl->orderBy($this->context, $column, $order);
        return new LimitRule($this->context);
    }
    private $impl;
}

class WhereRule extends OrderByRule
{
    public function __construct(Context $context, $isTheFirst = true)
    {
        parent::__construct($context);
        $this->isTheFirst = $isTheFirst;
    }

    /**
     * where('a=?', 1) => "WHERE a=1"
     * where('a=?', Sql::raw('now()')) => "WHERE a=now()"
     * where('a IN (?)',  [1, 2]) => "WHERE a IN (1,2)"
     * where([
     *      'a'=>1,
     *      'b'=>['IN'=>[1,2]]
     *      'c'=>['BETWEEN'=>[1,2]]
     *      'd'=>['<>'=>1]
     *      ])
     *      =>
     *      "WHERE a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1"
     *
     * @param string|array|callable $conditions
     * @param mixed $_
     * @return NextWhereRule
     */
    public function where($conditions=null, $_=null) {
        if(is_callable($conditions)){
            $callback = function ($context)use($conditions){
                $rule = new ScopedQuery($context);
                $conditions($rule);
            };
            $conditions = $callback;
        }
        if($this->isTheFirst){
            WhereImpl::where($this->context, 'WHERE' ,$conditions, array_slice(func_get_args(), 1));
        }else{
            WhereImpl::where($this->context, 'AND', $conditions, array_slice(func_get_args(), 1));
        }
        return new NextWhereRule($this->context, false);
    }

    protected $isTheFirst;
}

class NextWhereRule extends WhereRule
{
    /**
     * orWhere('a=?', 1) => "OR a=1"
     * orWhere('a=?', Sql::raw('now()')) => "OR a=now()"
     * orWhere('a IN (?)',  [1, 2]) => "OR a IN (1,2)"
     * orWhere([
     *      'a'=>1,
     *      'b'=>['IN'=>[1,2]]
     *      'c'=>['BETWEEN'=>[1,2]]
     *      'd'=>['<>'=>1]
     *      ])
     *      =>
     *      "OR (a=1 AND b IN(1,2) AND c BETWEEN 1 AND 2 AND d<>1)"
     *
     * @param string|array|callable $conditions
     * @param mixed $_
     * @return WhereRule
     */
    public function orWhere($conditions=null, $_=null) {
        if(is_callable($conditions)){
            $callback = function ($context)use($conditions){
                $rule = new ScopedQuery($context);
                $conditions($rule);
            };
            $conditions = $callback;
        }
        WhereImpl::where($this->context, 'OR', $conditions, array_slice(func_get_args(), 1));
        return new WhereRule($this->context, false);
    }
}

class ScopedQuery extends BasicRule
{

    public function __construct(Context $context, $isTheFirst = true)
    {
        parent::__construct($context);
        $this->isTheFirst = $isTheFirst;
    }

    /**
     * @param $expr
     * @param null $_
     * @return NextScopedQuery
     */
    public function where($expr, $_= null){
        if(is_callable($expr)){
            $callback = function ($context)use($expr){
                $rule = new ScopedQuery($context, true);
                $expr($rule);
            };
            $expr = $callback;
        }
        if($this->isTheFirst){
            WhereImpl::where($this->context, '', $expr, array_slice(func_get_args(), 1));
        }else{
            WhereImpl::where($this->context, 'AND', $expr, array_slice(func_get_args(), 1));
        }
        return new NextScopedQuery($this->context, false);
    }

    protected $isTheFirst;
}

class NextScopedQuery extends ScopedQuery
{
    /**
     * @param $expr
     * @param null $_
     * @return ScopedQuery
     */
    public function orWhere($expr, $_= null){
        if(is_callable($expr)){
            $callback = function ($context)use($expr){
                $rule = new ScopedQuery($context, true);
                $expr($rule);
            };
            $expr = $callback;
        }
        WhereImpl::where($this->context, 'OR', $expr, array_slice(func_get_args(), 1));
        return new NextScopedQuery($this->context, false);
    }

}