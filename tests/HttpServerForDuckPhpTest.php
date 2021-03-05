<?php 
namespace tests\WorkermanHttpd;

use WorkermanHttpd\HttpServerForDuckPhp;

use LibCoverage\LibCoverage;

class HttpServerForDuckPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        LibCoverage::Begin(HttpServerForDuckPhp::class);
        
        /* //
        HttpServerForDuckPhp::G()->init($options, $context = null);
        HttpServerForDuckPhp::G()->run();
        HttpServerForDuckPhp::G()->OnServerRequest();
        HttpServerForDuckPhp::G()->_OnServerRequest();
        //*/
        
        LibCoverage::End();
    }
}
