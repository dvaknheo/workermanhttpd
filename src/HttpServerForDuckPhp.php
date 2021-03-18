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
        //这里，把 ip-port 拼接上， 把事件连接上 //
        $listen = 'http://'.$this->options['host'].':'.$this->options['port'];
        
        //'path_document' => 'public',
        $workermann_options=[
            'listen' => $listen,
            'path' => App::G()->options['path'],
            'http_handler'=>[static::class,'OnServerRequest'],
        ];
        
        WorkermanHttpd::G()->init($workermann_options);
        
        App::G()->options['skip_404_handler'] = true;
        App::SetExceptionHandle(WorkermanHttpd404Exception::class,function(){});
        App::system_wrapper_replace(WorkermanServer::system_wrapper_get_providers());  //  替换系统函数
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
