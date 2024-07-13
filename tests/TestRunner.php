<?php

namespace Tests;

abstract class TestRunner extends \PHPUnit\Framework\TestCase
{

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
