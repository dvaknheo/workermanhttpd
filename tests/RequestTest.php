<?php 
namespace tests\WorkermanHttpd;

use WorkermanHttpd\Request;

use LibCoverage\LibCoverage;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        LibCoverage::Begin(Request::class);
        
        //* //
        //Request::G()->run();
        //*/
        
        LibCoverage::End();
    }
}
