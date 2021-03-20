# WorkermanHttpd

## 一、WorkerManHttpd 是什么

WorkermanHttpd 致力于 Workerman 代码和 fpm 平台 代码几乎不用修改就可以双平台运行。
是对 Workerman 类的一个包裹。

直接用 echo 输出。直接用超全局变量 $\_GET,$\_POST 等


只有少量系统函数改为 WorkermanHttpd 封装:

- header
- setcookie
- exit
- session_start
- session_id
- session_destroy
- session_set_save_handler


## 基本应用

### 使用方法：

```shell
composer require dvaknheo/workermanhttpd
```

```php
<?php
use WorkermanHttpd\WorkermanHttpd;
require(__DIR__.'/../autoload.php');
function hello()
{
    echo "<h1> hello ,have a good start.</h1><pre>\n";
    var_export($_GET,$_POST,$_SERVER);
    echo "</pre>";
    return true;
}

$options=[
    'port'=>8080,
    'http_handler'=>'hello',
];
WorkermanHttpd::RunQuickly($options);
```

浏览器打开 http://127.0.0.1:8080/
这个例子展现了 $_SERVER 里有的东西

### 选项

## 类解读

除了主类和 SingletonExTrait   其他都是无管理啊的

### ExitException

中断的异常类。一般不直接用，你需要 `WorkermanHttpd::Exit()`;

### HttpServerForDuckPhp

封装了 `DuckPhp\Http\Server` 的类 用于 DuckPhp 系统

### Request

可变单例请求类， 扩充自 `Workerman\Protocols\Http\Request` 使用 `SingletonExTrait`

### Response

可变单例请求类， 扩充自 `Workerman\Protocols\Http\Response` 使用 `SingletonExTrait`

### SingletonExTrait

可变单例类。 

### WorkermanHttpd

主类。主要调用这个。 使用 `SingletonExTrait`

#### 选项

包含 Workerman 的所有设置，

独特选项

    'host'  =>'127.0.0.1',          //绑定IP
    'port'  =>'8787',               //绑定端口

    'worker_name'            => 'WorkermanHttpd',       //标题
    'worker_count'           => -1,                 //CPU
    'worker_properties'      => [],                 //worker属性

    //////////
    //'pid_file'             => '???',
    //'stdout_file'          => '???',  
    'request_class'          => '',       //默认的请求类，一般不动。
    'command'                => 'start',  //对应命令
    'background'             => false,    //后台模式
    'gracefull'              => false,    //优雅模式

    'http_handler' => null,             //执行的 http_handler
    'http_handler_basepath' => '',      //暂未使用
    'http_handler_root' => null,        //暂未使用
    'http_handler_file' => null,        //暂未使用
    'http_exception_handler' => null,   //暂未使用
    'http_404_handler' => null,         //暂未使用

    'with_http_handler_root' => false,  //暂未使用
    'with_http_handler_file' => false,  //暂未使用

#### 静态方法:系统方法的替代

这些方法有

- header
- setcookie
- exit
- session_start
- session_id
- session_destroy
- session_set_save_handler

比如原先代码里有 `exit()`; 要修改成 `WorkermanHttpd::exit()`; 调用参数都一样
具体可以调用 `WorkermanHttpd::system_wrapper_get_providers()`显示了这些列表
#### 静态方法：子对象
WorkermanHttpd::Request()
    获得当前 Request 对象。
Response
    获得当前 Response 对象。
OnWorkerStart($worker)
    内部使用
OnMessage($connection, $request)
    内部使用

#### 动态方法

init()

run()