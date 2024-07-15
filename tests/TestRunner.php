<?php

namespace Tests;

abstract class TestRunner extends \PHPUnit\Framework\TestCase
{


    public string $postData = '{"verkehrsmittel":["HOCHGESCHWINDIGKEITSZUEGE","INTERCITYUNDEUROCITYZUEGE","INTERREGIOUNDSCHNELLZUEGE","NAHVERKEHRSONSTIGEZUEGE","SBAHNEN","BUSSE","UBAHN","STRASSENBAHN","ANRUFPFLICHTIGEVERKEHRE"],"datum":"2024-07-13","ursprungsBahnhofId":"A=1@O=Westentor, Hamm (Westf)@X=7813510@Y=51680100@U=80@L=902501@B=1@P=1720121116@","anfragezeit":"15:00"}';
    public array $headers = [
        'content-type: application/x.db.vendo.mob.bahnhofstafeln.v2+json',
        'accept: application/x.db.vendo.mob.bahnhofstafeln.v2+json',
        'x-correlation-id: BF275FF2-55A6-4240-91C3-CF0ECAF51B27_2BA4B348-D6A5-435B-8528-828C3959E54F',
    ];
    public string $url = 'https://app.vendo.noncd.db.de/mob/bahnhofstafel/abfahrt';

    public static function setUpBeforeClass(): void
    {

    }


    public function call(array $payload, string $host = "http://127.0.0.1", int $timeout = 30)
    {

        $curl = curl_init();


        $curlOpt = array(
            CURLOPT_URL => $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        );


        curl_setopt_array($curl, $curlOpt);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['error' => true, 'errorMsg' => $err];
        } else {
            return $response;
        }

    }

}
