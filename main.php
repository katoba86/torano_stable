<?php
use Commands\ResetCommand;
use Commands\StatusCommand;

use Symfony\Component\Console\Application;
use Commands\TorCommand;
use Commands\CheckCommand;

set_time_limit(0);
require __DIR__.'/vendor/autoload.php';


error_reporting(E_ALL);
ini_set("display_errors","on");


try {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();


}catch(\Dotenv\Exception\InvalidPathException $e){
    echo "No env File found... Starting auto system detect.\n";



    (new \Classes\AutoDetect())->run();
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    \Classes\AutoDetect::killAll();
    \Classes\AutoDetect::importResources();


    exit;
}

try {
    $app = new Application();
    $app->setCatchExceptions(false);
    $app->add(new TorCommand());
    $app->add(new CheckCommand());
    $app->add(new ResetCommand());

    $app->add(new StatusCommand());
    $app->run();
}catch(\Symfony\Component\Console\Exception\LogicException $e){
    echo "<pre>";print_r($e->getMessage());exit;
}
