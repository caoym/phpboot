<?php
/**
 * $Id: delete.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace caoym\ezsql\rules\delete;
use caoym\ezsql\rules\basic\BasicRule;
use caoym\ezsql\impls\DeleteImpl;
use caoym\ezsql\rules\basic\WhereRule;

require_once dirname(__DIR__).'/impls.php';
require_once __DIR__.'/basic.php';

class DeleteRule extends BasicRule
{
    /**
     * deleteFrom('table') => "DELETE FROM table"
     * @param string $table
     * @return \caoym\ezsql\rules\basic\WhereRule
     */
    public function deleteFrom($table) {
        DeleteImpl::deleteFrom($this->context, $table);
        return new WhereRule($this->context);
    }
}
