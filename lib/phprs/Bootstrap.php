<?php
namespace phprs;
use phprs\util\IoCFactory;
use phprs\util\exceptions\NotFound;
use phprs\util\exceptions\BadRequest;
use phprs\util\exceptions\Forbidden;
use phprs\util\exceptions\AuthenticationTimeout;
use phprs\util\exceptions\ExceptionWithHttpStatus;

class Bootstrap
{
    static public function run($conf_file) {

        $err = null;
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        //执行请求
        try {
            require_once __DIR__.'/AutoLoad.php';
            $factory  = new IoCFactory($conf_file);
            $router = $factory->create('phprs\\RouterWithCache');
            $router();
        }catch (NotFound $e) {
            header($protocol . ' 404 Not Found');
            $err = $e;
        }catch (BadRequest $e) {
            header($protocol . ' 400 Bad Request');
            $err = $e;
        }catch (Forbidden $e){
            header($protocol . ' 403 Forbidden');
            $err = $e;
        }catch (AuthenticationTimeout $e){
            header($protocol . ' 419 Authentication Timeout');
            $err = $e;
        }catch (ExceptionWithHttpStatus $e){
            header($protocol . ' '.$e->status);
            $err = $e;
        }catch(\Exception $e){
            header($protocol . ' 500 Internal Server Error');
            $err = $e;
        }
        if($err){
            header("Content-Type: application/json; charset=UTF-8");
            $estr = array(
                'error' => get_class($err),
                'message' => $err->getMessage(),
            );
            echo json_encode($estr);
        }
        
    }
}
