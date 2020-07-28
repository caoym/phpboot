<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/7/6
 * Time: 上午11:29
 */

namespace PhpBoot\DB\Exceptions;


use PhpBoot\DB\Context;
use Throwable;

class DBException extends \RuntimeException
{
    /**
     * @var string
     */
    private $sql;
    /**
     * @var array
     */
    private $params;

    public function __construct(Context $context, $message = "", $code = 0,$previous = null)
    {
        parent::__construct($message, $code, ($previous instanceof Throwable)?$previous:null);
        $this->sql = $context->sql;
        $this->params = $context->params;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
