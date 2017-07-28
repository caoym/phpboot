<?php
namespace PhpBoot\DB\rules\replace;

use PhpBoot\DB\rules\basic\BasicRule;
use PhpBoot\DB\rules\basic\ExecRule;
use PhpBoot\DB\impls\ReplaceImpl;
use PhpBoot\DB\impls\ValuesImpl;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class ReplaceIntoRule extends BasicRule
{
    /**
     * replaceInto('table')->values([1,2]) => "REPLACE INTO table VALUES(1,2)"
     * @param string $table
     * @return \PhpBoot\DB\rules\replace\ValuesRule
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
     * replaceInto('table')->values(['a'=>1, 'b'=>Sql::raw('now()')]) => "REPLACE INTO table(a,b) VALUES(1,now())"
     * @param array $values
     * @return \PhpBoot\DB\rules\basic\ExecRule
     */
    public function values($values) {
        ValuesImpl::values($this->context, $values);
        return new ExecRule($this->context);
    }
}