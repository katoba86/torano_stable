<?php

use Classes\Config;
use Classes\Connection;
use Classes\Fetch;
use Classes\Torano\ToranoConnection;
use Classes\Vpn\Vpn;
use Symfony\Component\Process\Process;

set_time_limit(30);
require __DIR__.'/vendor/autoload.php';
error_reporting(E_ALL);
ini_set("display_errors","on");








class Torano{

    /**
     * @var array
     */
    private $inputJSON;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $dumpCommand = false;

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

       $this->config = $this->getConfig($this->inputJSON);

        $this->fetch = $this->getFetch($this->inputJSON["fetch"]);

        return (new Connection($this->config,$this->fetch))->run();

    }

    private function getFetch($input): Fetch{
        $fetch = new Fetch();

        (isset($input["type"]))?$fetch->setMethod($input["type"]):null;
        (isset($input["url"]))?$fetch->setUrl($input["url"]):null;
        (isset($input["headers"]))?$fetch->setHeaders($input["headers"]):null;
        (isset($input["data"]))?$fetch->setData($input["data"]):null;
        (isset($input["contentType"]))?$fetch->setContentType($input["contentType"]):null;

        return $fetch;
    }


    private function getConfig($input):Config
    {
        if(!isset($input["config"])){
            return new Config();
        }else{

            $config = new Config();
            if(isset($input["config"]["type"])){
                switch($input["config"]["type"]){
                    case 'auto':
                        $config->type = Config::TYPE_AUTO;
                        break;
                    case 'tor':
                        $config->type = Config::TYPE_TOR;
                        break;
                    case 'vpn':
                        $config->type = Config::TYPE_VPN;
                        break;
                    default:
                        $config->type = Config::TYPE_AUTO;
                        break;
                }
            }

            (isset($input["config"]["country"]))    ?$config->country   = $input["config"]["country"]:  null;
            (isset($input["config"]["provider"]))   ?$config->provider  = $input["config"]["provider"]: null;
            (isset($input["config"]["retry"]))      ?$config->retry     = $input["config"]["retry"]:    null;
            (isset($input["config"]["timeout"]))    ?$config->timeout   = $input["config"]["timeout"]:  null;
            return $config;
        }
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
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
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
