<?php
namespace PhpBoot\Docgen;

use PhpBoot\Application;
use PhpBoot\Docgen\Swagger\Swagger;
use Symfony\Component\HttpFoundation\Response;

class DocgenProvider
{
    static public function register(Application $app, $prefix='/docs')
    {
        $app->addRoute('GET', $prefix.'/swagger.json', function (Application $app){
            $swagger = new Swagger();
            $swagger->appendControllers($app, $app->getControllers());
            return new Response($swagger->toJson());
        });
    }
}