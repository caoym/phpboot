<?php
namespace PhpBoot\DB\rules\update;
use PhpBoot\DB\rules\basic\BasicRule;
use PhpBoot\DB\rules\basic\WhereRule;
use PhpBoot\DB\impls\UpdateSetImpl;
use PhpBoot\DB\impls\UpdateImpl;


require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class UpdateRule extends BasicRule
{
    /**
     * update('table')->set('a', 1) => "UPDATE table SET a=1"
     * @param string $table
     * @return \PhpBoot\DB\rules\update\UpdateSetRule
     */
    public function update($table) {
        UpdateImpl::update($this->context, $table);
        return new UpdateSetRule($this->context);
    }
}

class UpdateSetRule extends BasicRule
{
    public function __construct($context){
        parent::__construct($context);
        $this->impl = new UpdateSetImpl();
    }
    /**
     * update('table')->set(['a'=>1]) => "UPDATE table SET a=1"

     * update('table')->set('a=?',1) => "UPDATE table SET a=1"
     * @param array|string $expr
     * @param mixed $_
     * @return WhereRule
     */
    public function set($expr, $_=null) {
        $this->impl->set($this->context, $expr, array_slice(func_get_args(), 1));
        return new WhereRule($this->context);
    }
    private $impl;
}



