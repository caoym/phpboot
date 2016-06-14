<?php
/**
 * $Id: update.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace phprs\ezsql\rules\update;
use phprs\ezsql\rules\basic\BasicRule;
use phprs\ezsql\rules\basic\WhereRule;
use phprs\ezsql\impls\UpdateSetImpl;
use phprs\ezsql\impls\UpdateImpl;


require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class UpdateRule extends BasicRule
{
    /**
     * update('table')->set('a', 1) => "UPDATE table SET a=1"
     * @param string $table
     * @return \phprs\ezsql\rules\update\UpdateSetRule
     */
    public function update($table) {
        UpdateImpl::update($this->context, $table);
        return new UpdateSetRule($this->context);
    }
}

class UpdateSetRule extends WhereRule
{
    public function __construct($context){
        parent::__construct($context);
        $this->impl = new UpdateSetImpl();
    }
    /**
     * update('table')->set('a', 1) => "UPDATE table SET a=1"
     * update('table')->set('a', 1)->set('b',Sql::native('now()')) => "UPDATE table SET a=1,b=now()"
     * @param string $column
     * @param mixed $value
     * @return \phprs\ezsql\rules\update\UpdateSetRule
     */
    public function set($column, $value) {
        $this->impl->set($this->context, $column, $value);
        return $this;
    }
    /**
     * update('table')->set(['a'=>1, 'b'=>Sql::native('now()')]) => "UPDATE table SET a=1,b=now()"
     * @param array $values
     * @return \phprs\ezsql\rules\update\UpdateSetRule
     */
    public function setArgs($values) {
        $this->impl->setArgs($this->context, $values);
        return $this;
    }
    private $impl;
}



