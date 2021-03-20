<?php declare(strict_types=1);
/**
 * WorkermanHttpd
 * From this time, you never be alone~
 */
namespace WorkermanHttpd;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\Worker;

///////////////////////////////

class WorkermanHttpd
{
    use SingletonExTrait;
    use WorkermanHttpd_SystemWrapper;
    protected static $_instances = [];
    ///////////////////
    public $options = [
        'host' => '127.0.0.1',
        'port' => '8787',
        
        'worker_name' => 'WorkermanHttpd',
        'worker_count' => -1,
        'worker_properties' => [],
        
        //////////
        //'pid_file'             => '???',
        //'stdout_file'          => '???',
        'path' => '',
        'request_class' => '',
        'command' => 'start',
        'background' => false,
        'gracefull' => false,
        
        
        'http_handler' => null,
        'http_handler_basepath' => '',
        'http_handler_root' => null,
        'http_handler_file' => null,
        'http_exception_handler' => null,
        'http_404_handler' => null,
        
        'with_http_handler_root' => false,
        'with_http_handler_file' => false,
    ];
    protected $worker;
    
    public function __construct()
    {
        //
    }
    public static function RunQuickly($options)
    {
        return static::G()->init($options)->run();
    }
    public function init(array $options, object $context = null)
    {
        $this->options['path'] = __DIR__;  // 这行有问题
        
        $this->options = array_replace_recursive($this->options, $options);
        //////////////////////
        $this->options['worker_count'] = $this->options['worker_count'] >= 0 ? $this->options['worker_count'] : $this->cpu_count() * 2;
        //TODO 采用默认配置
        //$this->options['pid_file'] = $this->options['path'] . '/runtime/webman.pid';
        //$this->options['stdout_file'] = $this->options['path']  . '/runtime/logs/stdout.log';
        
        ///////////////
        
        TcpConnection::$defaultMaxPackageSize = 10 * 1024 * 1024;
        //TcpConnection::$defaultMaxPackageSize = $this->options['max_request'];
        //Worker::$pidFile                      = $this->options['pid_file'];
        //Worker::$stdoutFile                   = $this->options['stdout_file'];
        
        Worker::$onMasterReload = [static::class,'OnMasterReload'];
        Worker::$daemonize = $this->options['background'];
        
        $this->worker = $this->initWorker();
        // 这里以后或许改成 MyWorker::G();
        
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
    protected function initWorker()
    {
        $listen = 'http://'.$this->options['host'].':'.$this->options['port'];
        $worker = new Worker($listen);
        $worker->name = $this->options['worker_name'];
        $worker->count = $this->options['worker_count'];
        foreach ($this->options['worker_properties'] as $k => $v) {
            if (isset($v)) {
                $worker->$k = $v;
            }
        }
        return $worker;
    }
    /**
     * @return int
     */
    protected function cpu_count()
    {
        if (strtolower(PHP_OS) === 'darwin') {
            $count = shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = shell_exec('nproc');
        }
        $count = (int)$count > 0 ? (int)$count : 4;
        return $count;
    }
    public function run()
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
            '-g' gracefull
        );
        */
        global $argv;
        $_SERVER['init_argv'] = $argv;
        $argv = [];
        $argv[0] = $_SERVER['init_argv'][0];
        $argv[] = $this->options['command'];
        if ($this->options['gracefull']) {
            $argv[] = '-g';
        }
        
        $this->worker->onWorkerStart = [static::class, 'OnWorkerStart'];
        Worker::runAll();
        return true;
    }
    public function OnMasterReload()
    {
        return static::G()->OnMasterReload();
    }
    public function _OnMasterReload()
    {
        //reload opcache
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
    public static function OnWorkerStart($worker)
    {
        return static::G()->_OnWorkerStart($worker);
    }
    public function _OnWorkerStart($worker)
    {
        Http::requestClass($this->options['request_class'] ? : Request::class);
        
        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
        register_shutdown_function(function ($start_time) {
            $this->endSession();
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
        
        ////
        $keep_alive = $request->header('connection');
        if (($keep_alive === null && $request->protocolVersion() === '1.1')
            || $keep_alive === 'keep-alive' || $keep_alive === 'Keep-Alive'
        ) {
            $connection->send(Response::G());
            Request::G(new \stdClass()); //free reference.
            Response::G(new \stdClass()); //free reference.
            return;
        }
        $connection->close($response);
        Request::G(new \stdClass()); //free reference.
        Response::G(new \stdClass()); //free reference.
    }
    protected function onRequest()
    {
        \ob_start();
        $flag = false;
        try {
            $flag = ($this->options['http_handler'])();
        } catch (\Exception $ex) {
            if ($this->options['http_exception_handler']) {
                ($this->options['http_exception_handler'])($ex);
            } else {
                static::header('', true, 500);
                echo $ex;
            }
        }
        return [$flag, \ob_get_clean()];
    }
    public static function Request()
    {
        return Request::G();
    }
    public static function Response()
    {
        return Response::G();
    }
    public static function Worker()
    {
        return $this->worker;
    }
    ////
    protected function doSuperGlobal($request)
    {
        $_GET = $request->get();
        $_POST = $request->post();
        $_REQUET = array_merge($_POST, $_GET);
        $_COOKIE = $request->cookie();
        
        $OLD_SERVER = $_SERVER ;
        //$_SERVER = [];
        $_SERVER['cli_script_filename'] = $OLD_SERVER['SCRIPT_FILENAME'] ?? '';
        $_SERVER['argv'] = $OLD_SERVER['init_argv'] ?? '';
        //$_SERVER['argv'] = $OLD_SERVER['init_argv'] ?? '';

        $_SERVER['REQUEST_URI'] = $request->uri();
        $_SERVER['REQUEST_METHOD'] = $OLD_SERVER['REQUEST_METHOD'] ?? 'GET';

        $_SERVER['PATH_INFO'] = \parse_url($request->uri(), PHP_URL_PATH);
        $_SERVER['DOCUMENT_ROOT'] = '';
        $_SERVER['SCRIPT_FILENAME'] = '';
        
        $headers = $request->header();
        foreach ($headers as $k => $v) {
            $k = 'HTTP_'.str_replace('-', '_', strtoupper($k));
            $_SERVER[$k] = $v;
        }
        //*/
    }
}


trait WorkermanHttpd_SystemWrapper
{
    public static function system_wrapper_get_providers()
    {
        $ret = [
            'header' => [static::class, 'header'],
            'setcookie' => [static::class, 'setcookie'],
            'exit' => [static::class, 'exit'],
            'session_start' => [static::class, 'session_start'],
            'session_id' => [static::class, 'session_id'],
            'session_destroy' => [static::class, 'session_destroy'],
            'session_set_save_handler' => [static::class, 'session_set_save_handler'],
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
        if ($http_response_code) {
            Response::G()->withStatus($http_response_code);
            return;
        }
        list($key, $value) = explode(':', $output);
        return Response::G()->header($key, $value);
    }
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return Response::G()->cookie($name, $value, $max_age, $path, $domain, $secure, $http_only);
    }

    public function _exit($code = 0)
    {
        throw new ExitException(''.$code, $code);
    }
    public function _session_start(array $options = [])
    {
        Request::G()->session();
        $_SESSION = Request::G()->session()->all();
    }
    public function _session_id($session_id = null)
    {
        return Request::G()->session()->getId();
    }
    public function _session_destroy()
    {
        Request::G()->session()->flush();
        Request::G()->session()->save();
        $_SESSION = null;
    }
    public function _session_set_save_handler(\SessionHandlerInterface $handler)
    {
        Request::G()->session()::handlerClass(get_class($handler));
    }
    public function endSession()
    {
        if (empty($_SESSION)) {
            return;
        }
        $t = $_SESSION;
        Request::G()->session()->flush();
        foreach ($t as $k => $v) {
            Request::G()->session()->set($k, $v);
        }
        Request::G()->session()->save();
    }
}
