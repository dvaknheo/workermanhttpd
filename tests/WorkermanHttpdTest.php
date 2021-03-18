<?php 
namespace tests\WorkermanHttpd;

use WorkermanHttpd\WorkermanHttpd;

use LibCoverage\LibCoverage;

class WorkermanHttpdTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        LibCoverage::Begin(WorkermanHttpd::class);
        
        // 我们要弄个背景执行，并设置 pid 文件，然后关闭 pid 文件。
        //WorkermanHttpd::RunQuickly($options);

        /* //
        WorkermanHttpd::G()->__construct();
        WorkermanHttpd::G()->RunQuickly($options);
        WorkermanHttpd::G()->init(array $options, object $context = null);
        WorkermanHttpd::G()->cpu_count();
        WorkermanHttpd::G()->ReloadOpCache();
        WorkermanHttpd::G()->_ReloadOpCache();
        WorkermanHttpd::G()->run();
        WorkermanHttpd::G()->onWorkerStart($worker);
        WorkermanHttpd::G()->_onWorkerStart($worker);
        WorkermanHttpd::G()->OnMessage($connection, $request);
        WorkermanHttpd::G()->_OnMessage($connection, $request);
        WorkermanHttpd::G()->onRequest();
        WorkermanHttpd::G()->bind_property($worker,$config);
        WorkermanHttpd::G()->Request();
        WorkermanHttpd::G()->Response();
        WorkermanHttpd::G()->doSuperGlobal($request);
        WorkermanHttpd::G()->system_wrapper_get_providers();
        WorkermanHttpd::G()->header($output, bool $replace = true, int $http_response_code = 0);
        WorkermanHttpd::G()->setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false);
        WorkermanHttpd::G()->exit($code = 0);
        WorkermanHttpd::G()->session_start(array $options = []);
        WorkermanHttpd::G()->session_id($session_id = null);
        WorkermanHttpd::G()->session_destroy();
        WorkermanHttpd::G()->session_set_save_handler(\SessionHandlerInterface $handler);
        WorkermanHttpd::G()->_header($output, bool $replace = true, int $http_response_code = 0);
        WorkermanHttpd::G()->_setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false);
        WorkermanHttpd::G()->_exit($code = 0);
        WorkermanHttpd::G()->_session_start(array $options = []);
        WorkermanHttpd::G()->_session_id($session_id = null);
        WorkermanHttpd::G()->_session_destroy();
        WorkermanHttpd::G()->_session_set_save_handler(\SessionHandlerInterface $handler);
        //*/
        
        LibCoverage::End();
    }
}
