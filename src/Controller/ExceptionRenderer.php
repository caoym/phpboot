<?php

namespace PhpBoot\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionRenderer
{
    /**
     * @param \Exception $e
     * @return Response
     */
    public function render(\Exception $e)
    {
        if($e instanceof HttpException){
            return new Response($e->getMessage(), $e->getStatusCode());
        }else{
            return new Response($e->getMessage(), 500);
        }
    }
}