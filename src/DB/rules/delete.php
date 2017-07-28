<?php
namespace PhpBoot\DB\rules\delete;
use PhpBoot\DB\rules\basic\BasicRule;
use PhpBoot\DB\impls\DeleteImpl;
use PhpBoot\DB\rules\basic\WhereRule;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class DeleteRule extends BasicRule
{
    /**
     * deleteFrom('table') => "DELETE FROM table"
     * @param string $table
     * @return \PhpBoot\DB\rules\basic\WhereRule
     */
    public function deleteFrom($table) {
        DeleteImpl::deleteFrom($this->context, $table);
        return new WhereRule($this->context);
    }
}
