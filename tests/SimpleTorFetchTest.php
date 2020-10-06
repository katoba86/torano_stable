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
    public function testGetGeraldProblem()
    {

        $payload=[
            'fetch'=>[
                'type'=>"GET",
                'url'=>"https://www.dhl.de/int-verfolgen/data/search?piececode=JJD000390011802112182&language=de%27",
                "headers"=>[
                    "User-Agent"=>"samsung\/lineage_hlte\/hlte:5.1.1\/NJH47F\/500190315:user\/de.flixbus.app\/4.3.2.4360",
                ]
            ]
        ];
        $testData = $this->call($payload);
        $this->assertTrue(strlen($testData)>100);
        $testDataDecoded = json_decode($testData,true);
        $this->assertTrue((is_array($testDataDecoded) && count($testDataDecoded)>=1));
    }


    /**
     * @test
     */
    public function testGetGeraldBigProblem()
    {

        $payload=[
            'fetch'=>[
                'type'=>"GET",
                'url'=>"https://www.logistics.dhl/utapi?trackingNumber=JJD000390013012044253&language=de&requesterCountryCode=DE#",
                "headers"=>[
                    "User-Agent"=>"samsung\/lineage_hlte\/hlte:5.1.1\/NJH47F\/500190315:user\/de.flixbus.app\/4.3.2.4360",
                ]
            ]
        ];
        $testData = $this->call($payload,"http://localhost/",20000);
        var_dump($testData);exit;
        $this->assertTrue(strlen($testData)>100);
        $testDataDecoded = json_decode($testData,true);
        $this->assertTrue((is_array($testDataDecoded) && count($testDataDecoded)>=1));
    }

}
