# WorkermanHttpd


[English](README.md) | [中文](README-zh-CN.md)

*** v1.0.2 ***

## what is WorkerManHttpd

wrap Workerman  for workerman platform and fpm platform

direct use echo  and super global $\_GET,$\_POST, and more


some system function need wrap,:

- exit
- header
- setcookie
- session_start
- session_id
- session_destro
- session_set_save_handler
- set_exception_handler (TODO)
- register_shutdown_function (TODO)

e.g. `session_start()`; => `WorkermanHttpd::session_start()`;
call `WorkermanHttpd::system_wrapper_get_providers()` to show


## Usage

```shell
composer require dvaknheo/workermanhttpd
```

```php
<?php
require(__DIR__.'/vendor/autoload.php');
function hello()
{
    \WorkermanHttpd\WorkermanHttpd::header('test: '.DATE(DATE_ATOM));
    echo "<h1> hello ,have a good start.</h1><pre>\n";
    var_dump($_GET,$_POST,$_SERVER);
    echo "</pre>";
    return true; //  正常true , 404 false;
}

$options=[
    'port'=>8080,
    'http_handler'=>'hello',
//* more default options
/*//
    //'host'  =>'127.0.0.1',          //
    //'port'  =>'8787',               //
    
    'worker_name'            => 'WorkermanHttpd', //
    'worker_count'           => -1,       //
    'worker_properties'      => [],       //
    'request_class'          => '',       // as Request::class
    'command'                => 'start',  // stop , reoad
    'background'             => false,    // -b
    'gracefull'              => false,    //  -g
 
    //// 
    //'http_handler' => null,           //执行的 http_handler
    'http_handler_basepath' => '',      //for next version
    'http_handler_root' => null,        //for next version
    'http_handler_file' => null,        //for next version
    'http_exception_handler' => null,   //for next version
    'http_404_handler' => null,         //for next version
    
    'with_http_handler_root' => false,  //for next version
    'with_http_handler_file' => false,  //for next version
//*/
];
\WorkermanHttpd\WorkermanHttpd::RunQuickly($options);
```

browse http://127.0.0.1:8080/
show  $_SERVER

## classes

 ...

### ExitException

not use direct use  `WorkermanHttpd::Exit()`;

### HttpServerForDuckPhp

wrap `DuckPhp\Http\Server` 
### Request

extends `Workerman\Protocols\Http\Request` use  `SingletonExTrait`

use Request::G(MyRequest::G()) to replace

### Response

extends `Workerman\Protocols\Http\Response` 使use  `SingletonExTrait`

use Request::G(MyRequest::G()) to replace

### SingletonExTrait

replace able trait , as in DuckPhp

### WorkermanHttpd

main class `SingletonExTrait`

#### static function

RunQuickly($options)

​	// as name

WorkermanHttpd::Request()
    get Request Object
Response
    get Request Object
OnWorkerStart($worker)
    ...
OnMessage($connection, $request)
    ...

G($object = null)

​	use WorkermanHttpd::G(MyWorkermanHttpd::G()) to replace

## note

php duckphp.php  run --override-class=WorkermanHttpd/HttpServerForDuckphp --command start