<?php 
namespace tests\WorkermanHttpd;

use WorkermanHttpd\SingletonExTrait;

use LibCoverage\LibCoverage;

class SingletonExTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        LibCoverage::Begin(SingletonExTrait::class);
        
        SingletonExObject::G();
        SingletonExObject::G(new SingletonExObject());
        $t=\LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',SingletonExObject::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        SingletonExObject::G();
        
        LibCoverage::End();
    }
}

class SingletonExObject
{
    use SingletonExTrait;
    
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }

}
