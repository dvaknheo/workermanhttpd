<?php declare(strict_types=1);
/**
 * WorkermanHttpd
 * From this time, you never be alone~
 */
namespace WorkermanHttpd;

use Workerman\Protocols\Http\Response as BaseResponse;
use WorkermanHttpd\SingletonExTrait;

class Response extends BaseResponse
{
    use SingletonExTrait;
    public function run()
    {
        //
    }
}
