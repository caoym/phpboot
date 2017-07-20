<?php

namespace PhpBoot\Controller;


use PhpBoot\Application;

class ExceptionHandler
{
    public function __construct()
    {
        $this->renderer = new ExceptionRenderer();
    }

    /**
     * @param string $name
     * @param string $doc
     */
    public function addExceptions($name, $doc)
    {
        $this->exceptions[] = [$name, $doc];
    }

    /*
     * @return array
     * 返回包含异常类型和描述的数组
     * 示例
     * [
     *      ['NotFoundHttpException', '这是说明'],
     *      ['ForbiddenHttpException', '这是说明'],
     * ]
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * @var array
     * 示例
     * [
     *      ['NotFoundHttpException', '这是说明'],
     *      ['ForbiddenHttpException', '这是说明'],
     * ]
     */
    private $exceptions = [];

    /**
     * @param Application $app
     * @param callable $call
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handler(Application $app, callable $call){
        try{
            return $call();
        }catch (\Exception $e){
            $renderer = $app->get(ExceptionRenderer::class); //TODO 放在这里是否合适
            return $renderer->render($e);
        }
    }
}