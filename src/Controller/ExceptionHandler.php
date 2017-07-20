<?php

namespace PhpBoot\Controller;


class ExceptionHandler
{
    public function __construct(ExceptionRenderer $renderer)
    {
        $this->renderer = $renderer;
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
     * @param callable $call
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handler(callable $call){
        try{
            return $call();
        }catch (\Exception $e){
            return $this->renderer->render($e);
        }
    }

    /**
     * @return ExceptionRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param ExceptionRenderer $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }
    /**
     * @var ExceptionRenderer;
     */
    private $renderer;

}