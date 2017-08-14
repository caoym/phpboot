<?php
namespace PhpBoot\Tests\Utils;

class RpcTestEntity2
{
    public function __construct()
    {
        $this->objArg = new RpcTestEntity1();
    }
    /**
     * @var int
     */
    public $intArg;
    /**
     * @var bool
     */
    public $boolArg;
    /**
     * @var float
     */
    public $floatArg;
    /**
     * @var string
     */
    public $strArg;
    /**
     * @var RpcTestEntity1
     */
    public $objArg;

    /**
     * @var RpcTestEntity1[]
     */
    public $arrArg;

    /**
     * @var string
     */
    public $defaultArg = 'default';
}