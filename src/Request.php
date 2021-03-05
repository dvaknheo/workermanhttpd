<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace WorkermanHttpd;
use WorkermanHttpd\SingletonExTrait;

use Workerman\Protocols\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    use SingletonExTrait;
    public function run()
    {
        //
    }
}