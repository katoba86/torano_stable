<?php


namespace Tests;


use Classes\Config;
use Classes\Fetch;
use Classes\Parse;

class FetchTest extends TestRunner
{

    /**
     * @test
     */
    public function parseBodyTest()
    {

        $payload=[
            'fetch'=>[
                'method'=>"GET",
                'url'=>"https://www.parcello.org",
                "headers"=>[
                    "User-Agent"=>"samsung\/lineage_hlte\/hlte:5.1.1\/NJH47F\/500190315:user\/de.flixbus.app\/4.3.2.4360",
                ]
            ],
            'config'=>[
                'timeout'=>10,
                'retry'=>2
            ]
        ];
        $parser = new Parse($payload);
        ray($parser->get('fetch.url'));
        //$this->assertInstanceOf(Config::class,$parser->getConfig());
        //$this->assertInstanceOf(Fetch::class,$parser->getFetch());


    }
    /**
     * @test
     */


}
