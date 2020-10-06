<?php


namespace Tests;


class SimpleTorFetchTest extends TestRunner
{

    /**
     * @test
     */
    public function getHttpsDataTest()
    {

        $payload=[
            'fetch'=>[
                'type'=>"GET",
                'url'=>"https://www.nrw-live.de",
                "headers"=>[
                    "User-Agent"=>"samsung\/lineage_hlte\/hlte:5.1.1\/NJH47F\/500190315:user\/de.flixbus.app\/4.3.2.4360",
                ]
            ]
        ];
        $test = $this->call($payload);

        $this->assertTrue(strlen($test)>500);
    }
    /**
     * @test
     */


}
