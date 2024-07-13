<?php


namespace Tests;


use Classes\Helper;
use Classes\Parse;
use Classes\Torano\Torano;
use Classes\Torano\TorConfig;
use Classes\Torano\TorElement;

class AbfahrtstafelTest extends TestRunner
{


    private $postData = '{"verkehrsmittel":["HOCHGESCHWINDIGKEITSZUEGE","INTERCITYUNDEUROCITYZUEGE","INTERREGIOUNDSCHNELLZUEGE","NAHVERKEHRSONSTIGEZUEGE","SBAHNEN","BUSSE","UBAHN","STRASSENBAHN","ANRUFPFLICHTIGEVERKEHRE"],"datum":"2024-07-13","ursprungsBahnhofId":"A=1@O=Westentor, Hamm (Westf)@X=7813510@Y=51680100@U=80@L=902501@B=1@P=1720121116@","anfragezeit":"15:00"}';
    private $headers = [
        'content-type: application/x.db.vendo.mob.bahnhofstafeln.v2+json',
        'accept: application/x.db.vendo.mob.bahnhofstafeln.v2+json',
        'x-correlation-id: BF275FF2-55A6-4240-91C3-CF0ECAF51B27_2BA4B348-D6A5-435B-8528-828C3959E54F',
    ];
    private $url = 'https://app.vendo.noncd.db.de/mob/bahnhofstafel/abfahrt';

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
        ray(json_encode($payload));

        $parse = new Parse($payload);
        $torano = new Torano();
        $torano->init($parse->getConfig(),$parse->getFetch());
        $data = $torano->getData();
        ray($data);
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
