<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace WorkermanHttpd;
use DuckPhp\HttpServer\HttpServer;
use DuckPhp\Core\App;

class HttpServerForDuckPhp extends  HttpServer
{
    public function init($options, $context = null)
    {
        $ret = parent::init($options,$context);

        
        //'path_document' => 'public',
        /*
        $workermann_options=[
            'host' => $this->options['host'],
            'port' => $this->options['port'],
            'path' => App::G()->options['path'],
            'http_handler'=>[static::class,'OnServerRequest'],
        ];
        */
        $options['port']=$options['port']?? 8080;
        $options['http_handler']=[static::class,'OnServerRequest'];
        WorkermanHttpd::G()->init($options);
        
        App::G()->options['skip_404_handler'] = true;
        App::assignExceptionHandler(WorkermanHttpd404Exception::class,function(){});
        App::system_wrapper_replace(WorkermanHttpd::system_wrapper_get_providers());  //  替换系统函数
        App::G()->replaceDefaultRunHandler(null); // 后续版本改为在系统里 replace
        
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
        return App::G()->run();
    }
}
