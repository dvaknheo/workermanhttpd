# WorkermanHttpd

[English](README.md) | [中文](README-zh-CN.md)

*** v1.0.2 ***

## WorkerManHttpd 是什么

WorkermanHttpd 致力于 Workerman 代码和 fpm 平台 代码几乎不用修改就可以双平台运行。
是对 Workerman 类的一个包裹。

直接用 echo 输出。直接用超全局变量 $\_GET,$\_POST 等


只有少量系统函数改为 WorkermanHttpd 封装，按字母排序有：

- exit
- header
- setcookie
- session_start
- session_id
- session_destro
- session_set_save_handler
- set_exception_handler (TODO)
- register_shutdown_function (TODO)

比如原先代码里有 `exit()`; 要修改成 `WorkermanHttpd::exit()`; 调用参数都一样
比如原先代码里有 `session_start()`; 要修改成 `WorkermanHttpd::session_start()`; 调用参数都一样
具体可以调用 `WorkermanHttpd::system_wrapper_get_providers()`看有什么


## 基本应用

```shell
composer require dvaknheo/workermanhttpd
```

```php
<?php
require(__DIR__.'/vendor/autoload.php');
function hello()
{
    \WorkermanHttpd\WorkermanHttpd::header('test',DATE(DATE_ATOM));
    echo "<h1> hello ,have a good start.</h1><pre>\n";
    var_dump($_GET,$_POST,$_SERVER);
    echo "</pre>";
    return true; //  正常true , 404 false;
}

$options=[
    'port'=>8080,
    'http_handler'=>'hello',
//* 更多的默认选项
/*//
    //'host'  =>'127.0.0.1',          //绑定IP
    //'port'  =>'8787',               //绑定端口
    
    'worker_name'            => 'WorkermanHttpd', //标题
    'worker_count'           => -1,       //CPU
    'worker_properties'      => [],       //给 Worker 类传递的属性
    'request_class'          => '',       //默认的请求类，一般不动。
    'command'                => 'start',  //对应命令 , stop 等
    'background'             => false,    //后台模式
    'gracefull'              => false,    //优雅模式
 
    //// 这段是几个服务器通用的
    //'http_handler' => null,           //执行的 http_handler
    'http_handler_basepath' => '',      //下版本再说， 资源目录
    'http_handler_root' => null,        //下版本再说，
    'http_handler_file' => null,        //下版本再说，
    'http_exception_handler' => null,   //下版本再说，对应set_exception_handler
    'http_404_handler' => null,         //下版本再说，404 处理
    
    'with_http_handler_root' => false,  //下版本再说，是否主目录 index.php
    'with_http_handler_file' => false,  //下版本再说，是否开启资源文件读取
//*/
];
\WorkermanHttpd\WorkermanHttpd::RunQuickly($options);
```

浏览器打开 http://127.0.0.1:8080/
这个例子展现了 $_SERVER 里有的东西

## 类解读

除了主类和 SingletonExTrait其他类都是无状态的

### ExitException

中断的异常类。一般不直接用，你需要 `WorkermanHttpd::Exit()`;

### HttpServerForDuckPhp

封装了 `DuckPhp\Http\Server` 的类 用于 DuckPhp 工程
### Request

可变单例请求类， 扩充自 `Workerman\Protocols\Http\Request` 使用 `SingletonExTrait`

可用 Request::G(MyRequest::G()) 替换

### Response

可变单例请求类， 扩充自 `Workerman\Protocols\Http\Response` 使用 `SingletonExTrait`

可用 Request::G(MyRequest::G()) 替换

### SingletonExTrait

可变单例类。 和 DuckPhp 的一样效果

### WorkermanHttpd

主类。主要调用这个。 使用 `SingletonExTrait`

#### 静态方法

RunQuickly($options)

​	快速运行

WorkermanHttpd::Request()
    获得当前 Request 对象。
Response
    获得当前 Response 对象。
OnWorkerStart($worker)
    事件处理
OnMessage($connection, $request)
    消息处理。

G($object = null)

​	可用 WorkermanHttpd::G(MyWorkermanHttpd::G()) 修改你的实现

## 备忘

php duckphp-project run --http-server=WorkermanHttpd/WorkermanHttpd  # --command start