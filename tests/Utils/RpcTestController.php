<?php
namespace PhpBoot\Tests\Utils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @path /tests
 */
class RpcTestController
{
    /**
     * @route GET /{intArg}/testRequestGet
     * @param int $intArg {@v min:1|max:2}
     * @param bool $boolArg
     * @param float $floatArg {@v min:1.1|max:2.1}
     * @param string $strArg
     * @param RpcTestEntity1 $objArg
     * @param string[] $arrArg
     * @param string $refArg
     * @param string $defaultArg
     * @param $mixedArg
     */
    public function testRequestGet($intArg, $boolArg, $floatArg, $strArg, $objArg, $arrArg, &$refArg, $mixedArg, $defaultArg='default')
    {

    }

    /**
     * @route POST /testRequestPost
     * @param int $intArg
     * @param bool $boolArg
     * @param float $floatArg
     * @param string $strArg
     * @param RpcTestEntity1 $objArg
     * @param string[] $arrArg
     * @param $refArg
     * @param string $defaultArg
     */
    public function testRequestPost($intArg, $boolArg, $floatArg, $strArg, $objArg, $arrArg, &$refArg, $defaultArg='default')
    {

    }

    /**
     * @route POST /testRequestPostWithBind
     * @param int $intArg {@bind request.query.intArg}
     * @param bool $boolArg {@bind request.headers.x-boolArg}
     * @param float $floatArg {@bind request.cookies.x-floatArg}
     * @param string $strArg {@bind request.request.strArg.strArg}
     * @param RpcTestEntity1 $objArg
     * @param string[] $arrArg
     * @param $refArg
     * @param string $defaultArg
     */
    public function testRequestPostWithBind($intArg, $boolArg, $floatArg, $strArg, $objArg, $arrArg, &$refArg, $defaultArg='default')
    {

    }


    /**
     * @route POST /testResponse
     * @param int $intArg
     * @param bool $boolArg {@bind response.headers."x-boolArg"}
     * @param float $floatArg {@bind response.headers."x-floatArg"}
     * @param string $strArg {@bind response.content.bindArg.bindArg}
     * @param RpcTestEntity1 $objArg
     * @param string[] $arrStrArg
     * @param $mixedArg
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @return RpcTestEntity2 response.content.data
     */
    public function testResponse($intArg, &$boolArg, &$floatArg, &$strArg, &$objArg, &$arrStrArg, &$mixedArg)
    {

    }

    /**
     * @route POST /testRefRequestWithoutBind
     * @param int $intArg
     * @param bool $boolArg
     * @param float $floatArg
     * @param string $strArg
     * @param RpcTestEntity1 $objArg
     * @param string[] $arrStrArg
     * @param $mixedArg
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @return RpcTestEntity2
     */
    public function testRefRequestWithoutBind($intArg, &$boolArg, &$floatArg, &$strArg, &$objArg, &$arrStrArg, &$mixedArg)
    {

    }

    /**
     * @route POST /testRequestWithoutFile
     * @param string $file1 {@bind request.files.file1}
     */
    public function testRequestWithoutFile($file1)
    {

    }

}
