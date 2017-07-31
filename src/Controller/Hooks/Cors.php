<?php

namespace PhpBoot\Controller\Hooks;
use PhpBoot\Controller\ExceptionRenderer;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Cors
 * copy from https://github.com/palanik/lumen-cors/blob/master/LumenCors.php
 */
class Cors
{
    use EnableDIAnnotations;

    protected $settings = array(
        'origin' => '*',    // Wide Open!
        'allowMethods' => 'GET,HEAD,PUT,POST,DELETE,PATCH,OPTIONS',
    );
    protected function setOrigin(Request $req, Response $rsp) {
        $origin = $this->settings['origin'];
        if (is_callable($origin)) {
            // Call origin callback with request origin
            $origin = call_user_func($origin,
                $req->headers->get("Origin")
            );
        }
        $rsp->headers->set('Access-Control-Allow-Origin', $origin);
    }
    protected function setExposeHeaders(Request $req, Response $rsp) {
        if (isset($this->settings->exposeHeaders)) {
            $exposeHeaders = $this->settings->exposeHeaders;
            if (is_array($exposeHeaders)) {
                $exposeHeaders = implode(", ", $exposeHeaders);
            }

            $rsp->headers->set('Access-Control-Expose-Headers', $exposeHeaders);
        }
    }
    protected function setMaxAge(Request $req, Response $rsp) {
        if (isset($this->settings['maxAge'])) {
            $rsp->headers->set('Access-Control-Max-Age', $this->settings['maxAge']);
        }
    }
    protected function setAllowCredentials(Request $req, Response $rsp) {
        if (isset($this->settings['allowCredentials']) && $this->settings['allowCredentials'] === True) {
            $rsp->headers->set('Access-Control-Allow-Credentials', 'true');
        }
    }
    protected function setAllowMethods(Request $req, Response $rsp) {
        if (isset($this->settings['allowMethods'])) {
            $allowMethods = $this->settings['allowMethods'];
            if (is_array($allowMethods)) {
                $allowMethods = implode(", ", $allowMethods);
            }

            $rsp->headers->set('Access-Control-Allow-Methods', $allowMethods);
        }
    }
    protected function setAllowHeaders(Request $req, Response $rsp) {
        if (isset($this->settings['allowHeaders'])) {
            $allowHeaders = $this->settings['allowHeaders'];
            if (is_array($allowHeaders)) {
                $allowHeaders = implode(", ", $allowHeaders);
            }
        }
        else {  // Otherwise, use request headers
            $allowHeaders = $req->headers->get("Access-Control-Request-Headers", null ,false);
        }
        if (isset($allowHeaders)) {
            $rsp->headers->set('Access-Control-Allow-Headers', $allowHeaders);
        }
    }
    protected function setCorsHeaders(Request $req, Response $rsp) {
        // http://www.html5rocks.com/static/images/cors_server_flowchart.png
        // Pre-flight
        if ($req->isMethod('OPTIONS')) {
            $this->setOrigin($req, $rsp);
            $this->setMaxAge($req, $rsp);
            $this->setAllowCredentials($req, $rsp);
            $this->setAllowMethods($req, $rsp);
            $this->setAllowHeaders($req, $rsp);
        }
        else {
            $this->setOrigin($req, $rsp);
            $this->setExposeHeaders($req, $rsp);
            $this->setAllowCredentials($req, $rsp);
        }
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next) {
        if ($request->getMethod() == 'OPTIONS') {
            $response = new Response("", 200);
        }
        else {
            try{
                $response = $next($request);
            }catch(\Exception $e){
                $response = $this->exceptionRenderer->render($e);
            }
        }
        $this->setCorsHeaders($request, $response);
        return $response;
    }

    /**
     * @inject
     * @var ExceptionRenderer
     */
    private $exceptionRenderer;
}