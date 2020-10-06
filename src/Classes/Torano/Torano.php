<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 07.06.2019
 * Time: 10:54
 */

namespace Classes\Torano;


use Classes\Config;
use Classes\ConnectorInterface;
use Classes\Fetch;
use Classes\Helper;
use Desarrolla2\Cache\Cache;
use Faker\Factory;
use Faker\Provider\UserAgent;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class Torano implements ConnectorInterface
{

    /**
     * @var null|Cache
     */
    private $cache = null;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var Fetch
     */
    public $fetch;

    /**
     * @var TorElement[]
     */
    public $proxys;

    /**
     * @var bool
     */
    public $success = false;

    /**
     * @var null|string
     */
    public $error = null;

    /**
     * @var TorElement
     */
    public $currentProxy;
    /**
     * @var array
     */
    private $lastInfos = [];

    /**
     * @inheritDoc
     */
    public function init(Config $config, Fetch $fetch): void
    {
       $this->config = $config;
       $this->fetch = $fetch;
        try {
            $this->cache = Helper::getCache();
        } catch (\RuntimeException $e) {
            echo "Error: No Cache enabled!";exit;
        }
        $this->proxys = $this->cache->get(TorConfig::SAVE_ARRAY);

        $this->currentProxy = $this->getFreeProxy();
        if(null === $this->currentProxy){
           $this->startSingleTorInstanceForCountry();
        }
    }


    private function startSingleTorInstanceForCountry()
    {
        $country = strtoupper($this->config->country);
        $cmd = "php ".$_SERVER["DOCUMENT_ROOT"]."/main.php torano:tor 1 ".$country;


        $process = new Process($cmd);
        $process->setTimeout(5);
        try {
            $process->run();
        }catch (ProcessTimedOutException $e){

            $this->proxys = $this->cache->get(TorConfig::SAVE_ARRAY);
            if(count($this->proxys) === 0){
                echo "Error: No proxys available";exit;
            }
            $this->currentProxy = $this->getFreeProxy();
            if(null === $this->currentProxy){
                echo "Error: Could not start new proxy";exit;
            }else{
                return;
            }

        }
        $this->proxys = $this->cache->get(TorConfig::SAVE_ARRAY);
        if(count($this->proxys) === 0){
            echo "Error: No proxys available";exit;
        }
        $this->currentProxy = $this->getFreeProxy();
        if(null === $this->currentProxy){
            echo "Error: Could not start new proxy";exit;
        }else{
            return;
        }


    }

    /**
     * @inheritDoc
     */
    public function getData()
    {

        $time_start = microtime(true);
// Script you want to test here

        if(is_array($this->fetch->getHeaders()) && isset($this->fetch->getHeaders()["User-Agent"])){
            $userAgent = $this->fetch->getHeaders()["User-Agent"];
        }else{
            $faker = Factory::create();
            $userAgent = $faker->userAgent;
        }

        if(is_array($this->fetch->getHeaders()) && !isset($this->fetch->getHeaders()["Host"])){

            $newHeaders = $this->fetch->getHeaders();
            $host = parse_url($this->fetch->getUrl(),PHP_URL_HOST);
            $newHeaders["Host"]=$host;
            $this->fetch->setHeaders($newHeaders);
        }

        $headers = Helper::buildHeadersForWget($this->fetch->getHeaders());

        $socksCommand = "torsocks -a 127.0.0.1 -P ".$this->currentProxy->getPort();


        $command = [
            $socksCommand,
            "wget --quiet --timeout=5",
            $headers,
            "--method GET ",
            "--header 'Accept-Encoding: gzip, deflate' ",
            "--header 'Connection: keep-alive' ",
            "--header 'cache-control: no-cache' ",
            "--output-document ",
            "- '".$this->fetch->getUrl()."'"
        ];
            $command = implode(" ",$command);




        $process = Process::fromShellCommandline($command);

        $res = "";

        $process->run(function ($type, $buffer) use ($process,&$res){
            if (Process::ERR !== $type) {
               $res.=$buffer;
           }
        });

        $time_end = microtime(true);
        $this->lastInfos = [
            'starttransfer_time' =>  ($time_end - $time_start)*1000
        ];
        $this->success=true;
        return Helper::decodeIfEncoded($res);
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
    //    $latency = 0;
      $latency = $this->lastInfos["starttransfer_time"];

      if(substr($latency,0,-2)==="ms"){
          $latency = substr($latency,0,strlen($latency)-2);
      }
      $latency = (float)$latency;


      $this->currentProxy->lastChecked = time();
      if($this->success) {
          $this->setProxySuccess($this->currentProxy, $latency);
      }else{
          $this->setProxyFailed($this->currentProxy,$latency,$this->error);
      }
    }


    public function setProxyFailed(TorElement $proxy,$latency,$msg = null){

        ///$proxy->setNumCalled($proxy->getNumCalled()+1);
        $proxy->setNumFailed($proxy->getNumFailed()+1);
        $proxy->setStatus(TorElement::STATUS_WARNING);
        if($msg!==null){
            $proxy->setLastError($msg);
        }
        $proxy->setLatency($latency);
        foreach($this->proxys as $index=>$_p){
            if($_p->pid === $proxy->pid){
                $this->proxys[$index] = $proxy;
            }
        }
        $this->cache->set(TorConfig::SAVE_ARRAY,$this->proxys,TorConfig::SAVE_TIME);
    }


    public function setProxySuccess(TorElement $proxy,$latency){

        $proxy->setNumSuccess($proxy->getNumSuccess()+1);
        $proxy->setStatus(TorElement::STATUS_OK);
        $proxy->setLatency($latency);
        foreach($this->proxys as $index=>$_p){
            if($_p->pid === $proxy->pid){
                $this->proxys[$index] = $proxy;
            }
        }
        $this->cache->set(TorConfig::SAVE_ARRAY,$this->proxys,TorConfig::SAVE_TIME);
    }



    /**
     * @return TorElement|null
     */
    public function getFreeProxy():?TorElement
    {
        $proxys = $this->proxys;
        $list = [];
        foreach($proxys as $index=>$proxy){
            /*@var $proxy TorElement */
            if($proxy->getStatus()!==TorElement::STATUS_ERR) {

                if(strtoupper($proxy->getCountry()) === strtoupper($this->config->country)) {
                    $list[$index] = $proxy->getNumSuccess();
                }
            }
        }
        if(count($list)===0){return null;}
        asort($list);
        $keys = array_keys($list);
        return $proxys[$keys[0]];


    }

}
