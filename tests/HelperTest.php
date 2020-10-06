<?php
namespace Tests;

use Classes\Helper;
use Classes\Torano\TorConfig;

class HelperTest extends TestRunner
{


    /**
     * @test
     */
    public function testSimpleCache(){

        $helper = Helper::getCache();
        $helper->set("name","test");
        $this->assertEquals("test", $helper->get("name"));
    }

    public function testGetToranosWithoutCache()
    {
        $this->assertIsArray( Helper::getToranoArray());
    }
    public function testGetVpnWithoutCache()
    {
        $this->assertIsArray( Helper::getVpnArray());
    }
}
