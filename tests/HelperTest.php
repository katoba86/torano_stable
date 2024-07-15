<?php
namespace Tests;

use Classes\Helper;
use Classes\Parse;
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

    public function testCachingKey(){
        $payload=[
            'fetch'=>[
                'method'=>"POST",
                'url'=>$this->url,
                "headers"=>$this->headers,
                "data"=>$this->postData,
            ]
        ];

        $parse = new Parse($payload);
        $fetch = $parse->getFetch();
        $key1 = $fetch->getCacheKey();
        $this->assertIsString($key1);


        $headers2 = $this->headers;
        $t = $headers2[count($headers2)-1];
        $headers2[count($headers2)-1] = $headers2[0];
        $headers2[0] = $t;
        $payload['fetch']['headers'] = $headers2;

        $parser = new Parse($payload);
        $fetch = $parser->getFetch();
        $key2 = $fetch->getCacheKey();
        $this->assertEquals($key2,$key1);






    }


}
