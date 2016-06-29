<?php
namespace phprs;
use phprs\util\IoCFactory;
use phprs\util\Logger;
use phprs\util\exceptions\NotFound;
use phprs\util\exceptions\BadRequest;
use phprs\util\exceptions\Forbidden;
use phprs\util\exceptions\NotAcceptable;
use phprs\util\exceptions\RateLimit;

class Bootstrap
{
    static public function run($conf_file) {
        
        require_once __DIR__.'/AutoLoad.php';
        
        $factory  = new IoCFactory($conf_file);        
        $router = $factory->create('phprs\\RouterWithCache');
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
    
        $err = null;
        
        try {
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
        }catch (NotAcceptable $e) {
            header($protocol . ' 406 Not Acceptable');  
            $err = $e;
        }catch (RateLimit $e) {
            header($protocol . ' 429 Too Many Requests');
            $err = $e;
        }catch (\Exception $e) {
            header($protocol . ' 500 Internal Server Error');   
            $err = $e;      
        }
        //print_r($err);           
        if($err){
            header("Content-Type: application/json; charset=UTF-8");                            
            $estr = [
                'message' => $err->getMessage(),
                'logs' => Logger::get_logs()
            ];
            //  $logs = ;
            //  if(!empty($logs)) $estr['logs'] = $logs;
                            
            echo json_encode($estr);
        }
    }
}
