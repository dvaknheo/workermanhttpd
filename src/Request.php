<?php declare(strict_types=1);
/**
 * WorkermanHttpd
 * From this time, you never be alone~
 */
namespace WorkermanHttpd;

use Workerman\Protocols\Http\Request as BaseRequest;
use WorkermanHttpd\SingletonExTrait;

class Request extends BaseRequest
{
    use SingletonExTrait;
}
