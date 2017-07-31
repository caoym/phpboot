<?php
namespace PhpBoot\Docgen\Swagger;

use PhpBoot\Application;
use Symfony\Component\HttpFoundation\Response;

class SwaggerProvider
{
    /**
     * How to use
     *
     * SwaggerProvider::register($app, function(Swagger $swagger){
     *          $swagger->host = 'api.example.com',
     *          $swagger->info->description = '...';
     *          ...
     *      },
     *      '/docs')
     *
     * @param Application $app
     * @param string $prefix
     * @param callable $callback
     * @return void
     */
    static public function register(Application $app,
                                    callable $callback = null,
                                    $prefix='/docs')
    {
        $app->addRoute('GET', $prefix.'/swagger.json', function (Application $app)use($callback){
            $swagger = new Swagger();
            $swagger->appendControllers($app, $app->getControllers());
            if($callback){
                $callback($swagger);
            }
            return new Response($swagger->toJson());
        });
    }
}