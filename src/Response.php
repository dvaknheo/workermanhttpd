<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace WorkermanHttpd;

use WorkermanHttpd\SingletonExTrait;
use Workerman\Protocols\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    use SingletonExTrait;
    public function run()
    {
        //
    }
}
