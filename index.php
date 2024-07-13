<?php

use Classes\Parse;
use Classes\Torano\Torano;
use League\Config\Exception\ValidationException as ConfigValidationException;

set_time_limit(30);
require __DIR__.'/vendor/autoload.php';
error_reporting(E_ALL);
ini_set("display_errors","on");


try {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
}catch(\Dotenv\Exception\InvalidPathException $e){
    echo "No env File found... Starting auto system detect.\n";
    (new \Classes\AutoDetect())->run();
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if(!is_array($data)){
    die("Not allowed without body");
}



$parse = new Parse($data);
$torano = new Torano();
try{
$torano->init($parse->getConfig(),$parse->getFetch());
}catch (ConfigValidationException $e){
    echo json_encode([
        'error'=>$e->getMessage(),
        'type'=>get_class($e)
    ]);exit;
}
try {
    echo $torano->getData();
}catch (\Exception $e){
    echo $e->getMessage();exit;
}



