<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/10/20
 * Time: 上午11:04
 */

namespace Once\Container;

use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use Once\Utils\ObjectAccess;
use Illuminate\Http\Response;

/**
 * Class Context
 * @package Once\Container
 * action runtime context
 */
class Context
{
    /**
     * Context constructor.
     * @param Application $app
     * @param Request $request
     */
    public function __construct(Application $app, Request $request){
        $this->app = $app;
        $this->request = $request;
        $this->response = new Response();

        //提取path中参数, 设置到Request中
        $curRoute = call_user_func($request->getRouteResolver());
        $pathParams = (is_array($curRoute)&&count($curRoute)>2)?$curRoute[2]:[];
        foreach ($pathParams as $k=>$v){
            $this->request[$k] = $v;
        }
    }

    /**
     * @param $path jsonpath描述的路径
     * @param null $default
     * @return array|mixed|null
     */
    public function getByPath($path){
        return $this->getAccessor()->get($path);

    }
    /**
     * @param $path jsonpath描述的路径
     * @return bool
     */
    public function hasPath($path){
        return $this->getAccessor()->has($path);
    }
    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }

    private function getAccessor(){
        $thiz = $this;
        return new ObjectAccess($this, [
            'request'=>[
                'route'=>function() use($thiz){
                    $curRoute = call_user_func($this->request->getRouteResolver());
                    $params = (is_array($curRoute)&&count($curRoute)>2)?$curRoute[2]:[];
                    foreach ($params as &$i){
                        //TODO 此处紧急修复因gateway问题, 需要剥离出此代码
                        //gateway会对path中的参数进行urldecode, 导致后端无法还原yrl
                        if(strpos($i, '%') !== false){
                            $i = urldecode($i);
                        }
                    }
                    return $params;
                },
                'session'=>function() use($thiz){
                    return $thiz->getRequest()->session()->all();
                },
                'query'=>function() use($thiz){
                    return $thiz->getRequest()->query->all();
                },
                'input'=>function() use($thiz){
                    return $thiz->getRequest()->input();
                },
                'files'=>function() use($thiz){
                    return $thiz->getRequest()->allFiles();
                },
                'headers'=>function() use($thiz){
                    return $thiz->getRequest()->headers->all();
                },
                'cookies'=>function() use($thiz){
                    return $thiz->getRequest()->cookies->all();
                },
            ]
        ]);
    }
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;


    /**
     * @var Application
     */
    private $app;
}