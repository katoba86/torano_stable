<?php
error_reporting(E_ALL);
ini_set("display_errors","on");
//print_r($_SERVER);
$output=[];
$output["ip"]=$_SERVER["REMOTE_ADDR"];
$record = geoip_record_by_name($_SERVER["REMOTE_ADDR"]);
if(is_array($record)){
    $geo=[
        'continent_code'=>$record["continent_code"],
        'country_code'=>$record["country_code"]
    ];

    $output=array_merge($output,$geo);
}
echo json_encode($output);
