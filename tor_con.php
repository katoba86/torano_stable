<?php

set_time_limit(30);
require __DIR__.'/vendor/autoload.php';
error_reporting(E_ALL);
ini_set("display_errors","on");

use Classes\ToranoConnection;



class Torano{

    /**
     * @var array
     */
    private $inputJSON;

    /**
     * @var string
     */
    private $response = "";

    public function __construct($input){
        $this->inputJSON = $input;
    }

    public function run(){
        if(!$this->check()){
            return $this->response;
        }
        return $this->parse($this->inputJSON["fetch"]);
    }



    private function parse($command){

        $type=$command["type"];
        $url = $command["url"];

        $contentType = null;
        $data = [];
        if(isset($command["content"])){
            $contentType = $command["content"];
        }
        if(isset($command["data"])){
            $data = $command["data"];
        }
        if(isset($command["headers"])){
            $headers = $command["headers"];
        }else{
            $headers=[];
        }


        $retrived = null;

        $connection = ToranoConnection::getInstance();
        if($type === "GET") {
            $retrived = $connection->getUrlByGet($url,$headers);
        }
        if($type === "POST") {
            $retrived = $connection->getUrlByPost($url,$contentType,$data,$headers);
        }




        return $retrived;
    }



    public function check(){
        if(!isset($this->inputJSON["fetch"]) || !is_array($this->inputJSON["fetch"])){
            $this->response="Wrong structure";
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }






    /**
     * @return mixed
     */
    public function getInputJSON()
    {
        return $this->inputJSON;
    }

    /**
     * @param mixed $inputJSON
     * @return Torano
     */
    public function setInputJSON($inputJSON)
    {
        $this->inputJSON = $inputJSON;
        return $this;
    }






}


try {
    $dotenv = new Dotenv\Dotenv(__DIR__);
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


echo (new Torano($data))->run();
