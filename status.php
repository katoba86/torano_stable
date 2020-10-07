<?php

use Classes\Helper;

set_time_limit(30);
require __DIR__.'/vendor/autoload.php';
error_reporting(E_ALL);
ini_set("display_errors","on");
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

class Status{

    public $status;
    public $msg;
    public $ok;
    public $err;
    public $num;
}

$toranos = Helper::getToranoArray();
if(!is_array($toranos) || count($toranos)===0){

    $status = new Status();
    $status->num = 0;
    $status->ok = false;
    $status->msg = "No toranos";

    echo json_encode($status,true);
}

$status = new Status();
$status->num = count($toranos);
foreach($toranos as $torano){
    $status->ok += $torano->getNumSuccess();
    $status->err += $torano->getNumFailed();
}
echo json_encode($status,true);