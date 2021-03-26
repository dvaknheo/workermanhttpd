<?php declare(strict_types=1);
/**
 * WorkermanHttpd
 * From this time, you never be alone~
 */
namespace WorkermanHttpd;

use Workerman\Worker as BaseWorker;
use WorkermanHttpd\SingletonExTrait;

class Worker extends BaseWorker
{
    use SingletonExTrait;
}
