<?php 
namespace tests\WorkermanHttpd;

use WorkermanHttpd\Response;

use LibCoverage\LibCoverage;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        LibCoverage::Begin(Response::class);
        
        //* //
        Response::G()->run();
        //*/
        
        LibCoverage::End();
    }
}
