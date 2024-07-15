<?php


namespace Tests;


use Classes\Helper;
use Classes\Parse;
use Classes\Torano\Torano;
use Classes\Torano\TorConfig;
use Classes\Torano\TorElement;

class AbfahrtstafelTest extends TestRunner
{



    /**
     * @test
     */
    public function getPlainData(): void
    {


        $curl = curl_init();


        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$this->postData,
            CURLOPT_HTTPHEADER => $this->headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $this->assertTrue(strlen($response)>500 && preg_match("/Bus/",$response));
    }


    public function testGetDataByToranoInstance():void{

        $payload=[
            'fetch'=>[
                'method'=>"POST",
                'url'=>$this->url,
                "headers"=>$this->headers,
                "data"=>$this->postData,
            ]
        ];

        $parse = new Parse($payload);
        $torano = new Torano();
        $torano->init($parse->getConfig(),$parse->getFetch());
        $data = $torano->getData();
        $this->assertTrue(strlen($data)>500 && preg_match("/Bus/",$data));
    }





    public function testGetTorInstance():void{
        $cache =   Helper::getCache();
        $torArray = $cache->get(TorConfig::SAVE_ARRAY);
        $this->assertIsArray($torArray);
        $this->assertGreaterThan(0,count($torArray));
        $this->assertInstanceOf(TorElement::class, $torArray[0]);
    }

    public function testGetDataBySocks():void{
        $curl = curl_init();
        $proxy = Helper::getRandomTorProxy();
        curl_setopt($curl,CURLOPT_PROXYTYPE,CURLPROXY_SOCKS5);
        curl_setopt($curl, CURLOPT_PROXY, "socks5://".$proxy->getUrl());
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$proxy->timeout ?? 5);


        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$this->postData,
            CURLOPT_HTTPHEADER => $this->headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $this->assertTrue(strlen($response)>500 && preg_match("/Bus/",$response));
    }


}
