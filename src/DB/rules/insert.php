<?php
namespace PhpBoot\DB\rules\insert;

use PhpBoot\DB\DB;
use PhpBoot\DB\impls\OnDuplicateKeyUpdateImpl;
use PhpBoot\DB\rules\basic\BasicRule;
use PhpBoot\DB\rules\basic\ExecRule;
use PhpBoot\DB\impls\InsertImpl;
use PhpBoot\DB\impls\ValuesImpl;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class InsertRule extends BasicRule
{
    /**
     * 
     * insertInto('table')->values([1,2]) => "INSERT INTO table VALUES(1,2)"
     * @param string $table
     * @return \PhpBoot\DB\rules\insert\ValuesRule
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
     * insertInto('table')->values(['a'=>1, 'b'=>Sql::raw('now()')]) => "INSERT INTO table(a,b) VALUES(1,now())"
     * @param array $values
     * @return \PhpBoot\DB\rules\insert\OnDuplicateKeyUpdateRule
     */
    public function values(array $values) {
        ValuesImpl::values($this->context, $values);
        return new OnDuplicateKeyUpdateRule($this->context);
    }

    /**
     * insertInto('table')->batchValues([[1,2],[3,4]]) => "INSERT INTO table VALUES(1,2), (3,2)"
     *
     * @param array $values
     * @return \PhpBoot\DB\rules\insert\OnDuplicateKeyUpdateRule
     */
    public function batchValues(array $values){
        ValuesImpl::batchValues($this->context, $values);
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

//    /**
//     *
//     * insertInto('table')
//     *      ->values(['a'=>1, 'b'=>Sql::raw('now()')])
//     *      ->onDuplicateKeyUpdate('a', Sql::raw('a+1'))
//     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
//     *
//     * @param string $column
//     * @param mixed $value
//     * @return \PhpBoot\DB\rules\basic\ExecRule
//     */
//    public function onDuplicateKeyUpdate($column, $value) {
//        $this->impl->set($this->context, $column, $value);
//        return new ExecRule($this->context);
//    }

//    /**
//     *
//     * insertInto('table')
//     *      ->values(['a'=>1, 'b'=>Sql::raw('now()')])
//     *      ->onDuplicateKeyUpdateArgs(['a'=>Sql::raw('a+1')])
//     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
//     *
//     * @param string $column
//     * @param mixed $value
//     * @return \PhpBoot\DB\rules\basic\ExecRule
//     */
//    public function onDuplicateKeyUpdateArgs($values) {
//        $this->impl->setArgs($this->context, $values);
//        return new ExecRule($this->context);
//    }

    /**
     *
     *  insertInto('table')
     *      ->values(['a'=>1, 'b'=>Sql::raw('now()')])
     *      ->onDuplicateKeyUpdate(['a'=>Sql::raw('a+1')])
     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * insertInto('table')
     *      ->values(['a'=>1, 'b'=>Sql::raw('now()')])
     *      ->onDuplicateKeyUpdate('a=a+1')
     *  => "INSERT INTO table(a,b) VALUES(1,now()) ON DUPLICATE KEY UPDATE a=a+1"
     *
     * @param string $column
     * @param mixed $value
     * @return \PhpBoot\DB\rules\basic\ExecRule
     */
    public function onDuplicateKeyUpdate($expr, $_=null) {
        $this->impl->set($this->context, $expr, array_slice(func_get_args(), 1));
        return new ExecRule($this->context);
    }
    private $impl;
}