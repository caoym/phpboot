<?php
namespace phprs\ezsql\rules\replace;

use phprs\ezsql\rules\basic\BasicRule;
use phprs\ezsql\rules\basic\ExecRule;
use phprs\ezsql\impls\ReplaceImpl;
use phprs\ezsql\impls\ValuesImpl;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class ReplaceIntoRule extends BasicRule
{
    /**
     * replaceInto('table')->values([1,2]) => "REPLACE INTO table VALUES(1,2)"
     * @param string $table
     * @return \phprs\ezsql\rules\replace\ValuesRule
     */
    public function replaceInto($table) {
        ReplaceImpl::replaceInto($this->context, $table);
        return new ValuesRule($this->context);
    }
}
class ValuesRule extends BasicRule
{
    /**
     * replaceInto('table')->values([1,2]) => "REPLACE INTO table VALUES(1,2)"
     * replaceInto('table')->values(['a'=>1, 'b'=>Sql::native('now()')]) => "REPLACE INTO table(a,b) VALUES(1,now())"
     * @param unknown $values
     * @return \phprs\ezsql\rules\basic\ExecRule
     */
    public function values($values) {
        ValuesImpl::values($this->context, $values);
        return new ExecRule($this->context);
    }
}