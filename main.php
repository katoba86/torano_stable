<?php

use Classes\AutoDetect;
use Commands\StatusCommand;

use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;
use Symfony\Component\Console\Application;
use Commands\TorCommand;
use Commands\CheckCommand;

set_time_limit(0);
require __DIR__.'/vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors","on");


function autoDetect(){

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    (new AutoDetect())->run();
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    AutoDetect::killAll();

}

try {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    $dotenv->required(['cache','ip']);



}catch(InvalidPathException $e){
    echo "No env File found... Starting auto detect.\n";
    autoDetect();
    exit;
}catch(ValidationException $e){

    echo "env File has missing entries...Starting auto detect";
    autoDetect();
    exit;
}

try {
    $app = new Application();
    $app->setCatchExceptions(false);
    $app->add(new TorCommand());
    $app->add(new CheckCommand());


    $app->add(new StatusCommand());
    $app->run();
}catch(\Symfony\Component\Console\Exception\LogicException $e){
    echo "<pre>";print_r($e->getMessage());exit;
}
