<?php

use MaxMind\Db\Reader;

error_reporting(E_ALL);
ini_set("display_errors","on");

require 'vendor/autoload.php';


$reader = new Reader('geo.mmdb');


//print_r($_SERVER);
$output=[];
$output["ip"]=$_SERVER["REMOTE_ADDR"];
$record = $reader->get($output["ip"]);
if(is_array($record)){
    $geo=[
        'continent_code'=>$record["continent"]["code"],
        'country_code'=>$record["country"]["iso_code"]
    ];

    $output=array_merge($output,$geo);
}
echo json_encode($output);
// get returns just the record for the IP address
