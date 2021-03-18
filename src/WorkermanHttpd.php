<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace WorkermanHttpd;

use Workerman\Worker;
use Workerman\Protocols\Http;
use Workerman\Connection\TcpConnection;

///////////////////////////////

class WorkermanHttpd
{
    use SingletonExTrait;
    use WorkermanHttpd_SystemWrapper;
    protected static $_instances = [];
    ///////////////////
    public $options = [
        'listen'               => 'http://127.0.0.1:8787',
        'context'              => [],
        'name'                 => 'WorkermanHttpd',
        'user'                 => '',
        'group'                => '',
        'max_request'          => 1000000,
        'max_package_size'     => 10*1024*1024,
        'process'              => [],
        'autoload_files'       => [],
        'bootstrap'            => [],
        
        //////////
        'count'                => -1,
        'pid_file'             => '???',
        'stdout_file'          => '???',
        /////
        'path'                 => '???',
        'command'              => '',
        /////////////
    ];
    
    public function __construct()
    {
/*
$available_commands = array(
    'start',
    'stop',
    'restart',
    'reload',
    'status',
    'connections',
);
$available_mode = array(
    '-d',
    '-g'
);
*/
        $this->options['pid_file'] =  'runtime/webman.log';
        $this->options['stdout_file'] =  'runtime/logs/stdout.log';
    }
    public static function RunQuickly($options)
    {
        return static::G()->init($options)->run();
    }
    public function init(array $options, object $context = null)
    {
        $this->options['path'] =__DIR__;
        $this->options = array_replace_recursive($this->options, $options);
        
        $this->options['count'] = $this->options['count'] >=0 ? $this->options['count'] : $this->cpu_count() * 2;

        $this->options['pid_file'] = $this->options['path'] . '/runtime/webman.pid';
        $this->options['stdout_file'] = $this->options['path']  . '/runtime/logs/stdout.log';
        
        Worker::$pidFile                      = $this->options['pid_file'];
        Worker::$stdoutFile                   = $this->options['stdout_file'];
        TcpConnection::$defaultMaxPackageSize = $this->options['max_package_size'];
        Worker::$onMasterReload               = [static::class,'ReloadOpCache'];
        return $this;
    }
    protected function getComponenetPathByKey($path_key): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($this->options[$path_key], 0, 1) === '/') {
                return rtrim($this->options[$path_key], '/').'/';
            } else {
                return $this->options['path'].rtrim($this->options[$path_key], '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($this->options[$path_key], 1, 1) === ':') {
                return rtrim($this->options[$path_key], '\\').'\\';
            } else {
                return $this->options['path'].rtrim($this->options[$path_key], '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
    }
    /**
     * @return int
     */
    protected function cpu_count() {
        if (strtolower(PHP_OS) === 'darwin') {
            $count = shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = shell_exec('nproc');
        }
        $count = (int)$count > 0 ? (int)$count : 4;
        return $count;
    }
    ////////////
    public function ReloadOpCache()
    {
        return static::G()->_ReloadOpCache();
    }
    public function _ReloadOpCache()
    {
        if (function_exists('opcache_get_status')) {
            if ($status = opcache_get_status()) {
                if (isset($status['scripts']) && $scripts = $status['scripts']) {
                    foreach (array_keys($scripts) as $file) {
                        opcache_invalidate($file, true);
                    }
                }
            }
        }
    }
    ////////////////////////////////////////////
    public function run()
    {
        //global $argv;
        //$argv[1] ='start';
        
        $config = $this->options;
        $worker = new Worker($config['listen'], $config['context']);
        
        $property_map = [
            'name',
            'user',
            'group',
            'reusePort',
        ];
        $this->bind_property($worker,$config,$property_map);
        //$worker->$property = $config[$property];
        $worker->onWorkerStart = [static::class, 'onWorkerStart'];
        Worker::runAll();
        return true;
    }
    
    public static function onWorkerStart($worker)
    {
        return static::G()->_onWorkerStart($worker);
    }
    public function _onWorkerStart($worker)
    {
        Http::requestClass(Request::class); // 这里要做成可配置的。
        
        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
        register_shutdown_function(function ($start_time) {
            if (time() - $start_time <= 1) {
                echo "\nWait to gracefull end \n";
                sleep(1);
            }
        }, time());
        $worker->onMessage = [static::class,'OnMessage'];
        
    }
    public static function OnMessage($connection, $request)
    {
        return static::G()->_OnMessage($connection, $request);
    }

    public function _OnMessage($connection, $request)
    {
        Request::G($request)->run();
        Response::G(new Response())->run();
        $this->doSuperGlobal($request);
        list($flag, $data) = $this->onRequest();
        Response::G()->withBody($data);
        
        $keep_alive =  $request->header('connection');
        if (($keep_alive === null && $request->protocolVersion() === '1.1')
            || $keep_alive === 'keep-alive' || $keep_alive === 'Keep-Alive'
        ) {
            $connection->send(Response::G());
            Response::G(new Response()); // free 掉
            return;
        }
        $connection->close($response);
        Response::G(new Response()); // free 掉
    }
    protected function onRequest()
    {
        \ob_start();
        $flag = false;
        try {
            $callback = $this->options['http_handler'];
            $flag = $callback();  // 这里要保存是否 404
        } catch (\Exception $e) {
            echo $e;  // 这里应该让其他地方处理
        }
        return [$flag, \ob_get_clean()];
    }
    ////////////////////////////////////
    protected function bind_property($worker,$config,$property_map)
    {
        foreach ($property_map as $property) {
            if (isset($config[$property])) {
                $worker->$property = $config[$property];
            }
        }
    }
    /////////////////////////////////
    
    public static function Request()
    {
        return Request::G();
    }
    public static function Response()
    {
        return Response::G();
    }
    ////
    public function doSuperGlobal($request)
    {
        //$request = 
        //$_ENV,
        $_GET = $request->get();
        $_POST = $request->post();
        $_REQUET = array_merge($_POST,$_GET);
        $_COOKIE = $request->cookie();
        
        $_SERVER = [];
        $_SERVER['REQUEST_URI']=$request->uri();
        $_SERVER['REQUEST_METHOD']= $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $_SERVER['PATH_INFO']=\parse_url($request->uri(), PHP_URL_PATH);
        $_SERVER['DOCUMENT_ROOT']='';
        $_SERVER['SCRIPT_FILENAME']='';
        
        //session 的处理？
        //$_SESSION;
        //////
        /*
        if (isset($this->_SERVER['argv'])) {
            $this->_SERVER['cli_argv'] = $this->_SERVER['argv'];
            unset($this->_SERVER['argv']);
        }
        if (isset($this->_SERVER['argc'])) {
            $this->_SERVER['cli_argc'] = $this->_SERVER['argc'];
            unset($this->_SERVER['argc']);
        }
        
        //$headers = $this->header();
        foreach ($headers as $k => $v) {
            $k = 'HTTP_'.str_replace('-', '_', strtoupper($k));
            $_SERVER[$k] = $v;
        }
        $this->_SERVER['cli_script_filename'] = $this->_SERVER['SCRIPT_FILENAME'] ?? '';
        //*/
    }
}


trait WorkermanHttpd_SystemWrapper
{
    public function system_wrapper_get_providers()
    {
        $ret = [
            'header'=>[static::class, 'header'],
            'setcookie'=>[static::class, 'setcookie'],
            'exit'=>[static::class, 'exit'],
            'session_start'=>[static::class, 'session_start'],
            'session_id'=>[static::class, 'session_id'],
            'session_destroy'=>[static::class, 'session_destroy'],
            'session_set_save_handler'=>[static::class, 'session_set_save_handler'],
        ];
        return $ret;
    }
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return static::G()->_exit($code);
    }
    public static function session_start(array $options = [])
    {
        return static::G()->_session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return static::G()->_session_id($session_id);
    }
    public static function session_destroy()
    {
        return static::G()->_session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return static::G()->_session_set_save_handler($handler);
    }
    ////////////////////////////////////////
    public function _header($output, bool $replace = true, int $http_response_code = 0)
    {
        list($key, $value) = explode(':', $output);
        if($http_response_code){
            Response::G()->withStatus($http_response_code);
        }
        return Response::G()->header($key, $value);
    }
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return Response::G()->cookie($name, $value, $max_age, $path, $domain, $secure, $http_only);
    }

    public function _exit($code = 0)
    {
        throw new ExitException($code, $code); // 这里要有一个规范的 Exception.
    }
    ////[[[[
    public function _session_start(array $options = [])
    {
        // 这里要额外处理。
        Request::G()->session_id();
    }
    public function _session_id($session_id = null)
    {
        return Request::G()->session_id();
    }
    public function _session_destroy()
    {
        Request::G()->session()->save();
    }
    public function _session_set_save_handler(\SessionHandlerInterface $handler)
    {
        Request::G()->session()::handlerClass(get_class($handler));
    }
    ////]]]]
}