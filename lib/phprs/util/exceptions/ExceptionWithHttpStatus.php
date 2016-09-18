<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/8/25
 * Time: 上午10:38
 */

namespace phprs\util\exceptions;


class ExceptionWithHttpStatus extends \Exception
{
    public $status;
}