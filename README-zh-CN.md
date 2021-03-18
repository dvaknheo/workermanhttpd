# WorkerManHttpd

** 准备用来做 WorkerMan 的封装**
## WorkerManHttpd 是什么

WorkerManHttpd 致力于 WorkerMan 代码和 fpm 平台 代码几乎不用修改就可以双平台运行。
是对 workderman 类的一个包裹。



理论上应该是是高性能的

## 特色

直接用 echo 输出。直接用超全局变量 $\_GET,$\_POST 等

最方便旧代码迁移。


只需要系统函数的封装 WorkerManHttpd ::header(),WorkerManHttpd ::setcookie() 等。

尤其是 WorkerManHttpd ::session_start()


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
