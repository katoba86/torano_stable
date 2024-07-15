<?php


namespace Tests;


use Classes\Helper;
use Classes\Parse;
use Classes\Torano\Torano;

class SimpleTorFetchTest extends TestRunner
{

    /**
     * @test
     */
    public function getHttpsDataTest()
    {

        $payload=[
            'fetch'=>[
                'method'=>"GET",
                'url'=>"https://www.parcello.org",
                "headers"=>[
                    "User-Agent"=>"samsung\/lineage_hlte\/hlte:5.1.1\/NJH47F\/500190315:user\/de.flixbus.app\/4.3.2.4360",
                ]
            ]
        ];

        $parse = new Parse($payload);
        $fetch = $parse->getFetch();
        $assumedKey = $fetch->getCacheKey();
        $torano = new Torano();
        $torano->init($parse->getConfig(),$fetch);
        $test = $torano->getData();
        $this->assertTrue(!is_array($test) && strlen($test)>500 && preg_match("/Sendung/",$test));



        $cache = Helper::getCache();
        $data = $cache->get($assumedKey);
        $this->assertEquals($test,$data,'Data is present in cache');
        $time = time();
        $test3 = $torano->getData();
        $this->assertLessThan(3,time()-$time);
        $this->assertEquals($test3,$test,'Data is present in all variations in cache');


    }



}
