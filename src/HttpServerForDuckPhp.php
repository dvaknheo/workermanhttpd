<?php declare(strict_types=1);
/**
 * WorkermanHttpd
 * From this time, you never be alone~
 */
namespace WorkermanHttpd;

use DuckPhp\Core\App;
use DuckPhp\HttpServer\HttpServer;

class HttpServerForDuckPhp extends HttpServer
{
    public function init($options, $context = null)
    {
        $ret = parent::init($options, $context);
        $options['port'] = $options['port'] ?? 8080;
        $options['http_handler'] = [static::class,'OnServerRequest'];
        WorkermanHttpd::G()->init($options);
        
        App::G()->options['skip_404_handler'] = true;
        App::assignExceptionHandler(ExitException::class, function () {
        });
        App::system_wrapper_replace(WorkermanHttpd::system_wrapper_get_providers());
        
        ///////////
        return $ret;
    }
    public function run()
    {
        return WorkermanHttpd::G()->run();
    }
    ////
    public static function OnServerRequest()
    {
        return static::G()->_OnServerRequest();
    }
    public function _OnServerRequest()
    {
        //还看看有什么额外操作
        $flag = App::G()->run();
        if (!$flag) {
            App::On404();
        }
        return true;
    }
}
