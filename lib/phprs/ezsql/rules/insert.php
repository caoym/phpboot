<?php
/**
 * $Id: insert.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace phprs\ezsql\rules\insert;

use phprs\ezsql\impls\OnDuplicateKeyUpdateImpl;
use phprs\ezsql\rules\basic\BasicRule;
use phprs\ezsql\rules\basic\ExecRule;
use phprs\ezsql\impls\InsertImpl;
use phprs\ezsql\impls\ValuesImpl;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class InsertRule extends BasicRule
{
    /**
     * 
     * insertInto('table')->values([1,2]) => "INSERT INTO table VALUES(1,2)"
     * @param string $table
     * @return \phprs\ezsql\rules\insert\ValuesRule
     */
    public function insertInto($table) {
        InsertImpl::insertInto($this->context, $table);
        return new ValuesRule($this->context);
    }
}
class ValuesRule extends BasicRule
{
    /**
     *
     * insertInto('table')->values([1,2]) => "INSERT INTO table VALUES(1,2)"
     * insertInto('table')->values(['a'=>1, 'b'=>Sql::native('now()')]) => "INSERT INTO table(a,b) VALUES(1,now())"
     * @param unknown $values
     * @return \phprs\ezsql\rules\insert\OnDuplicateKeyUpdateRule
     */
    public function values($values) {
        ValuesImpl::values($this->context, $values);
        return new OnDuplicateKeyUpdateRule($this->context);
    }
}

class OnDuplicateKeyUpdateRule extends ExecRule
{
    public function __construct($context)
    {
        parent::__construct($context);
        $this->impl = new OnDuplicateKeyUpdateImpl();
    }

    /**
     *
     * insertInto('table')
     *      ->values(['a'=>1, 'b'=>Sql::native('now()')])
     *      ->onDuplicateKeyUpdate('a', Sql::native('a+1'))
     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column
     * @param mixed $value
     * @return \phprs\ezsql\rules\basic\ExecRule
     */
    public function onDuplicateKeyUpdate($column, $value) {
        $this->impl->set($this->context, $column, $value);
        return new ExecRule($this->context);
    }

    /**
     *
     * insertInto('table')
     *      ->values(['a'=>1, 'b'=>Sql::native('now()')])
     *      ->onDuplicateKeyUpdateArgs(['a'=>Sql::native('a+1')])
     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column
     * @param mixed $value
     * @return \phprs\ezsql\rules\basic\ExecRule
     */
    public function onDuplicateKeyUpdateArgs($values) {
        $this->impl->setArgs($this->context, $values);
        return new ExecRule($this->context);
    }

    /**
     *
     * insertInto('table')
     *      ->values(['a'=>1, 'b'=>Sql::native('now()')])
     *      ->onDuplicateKeyUpdateExpr('a=a+1')
     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column
     * @param mixed $value
     * @return \phprs\ezsql\rules\basic\ExecRule
     */
    public function onDuplicateKeyUpdateExpr($expr, $_=null) {
        $this->impl->setExpr($this->context, $expr, array_slice(func_get_args(), 1));
        return new ExecRule($this->context);
    }
    private $impl;
}