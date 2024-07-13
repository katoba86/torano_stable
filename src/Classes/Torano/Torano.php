<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 07.06.2019
 * Time: 10:54
 */

namespace Classes\Torano;


use Classes\Config;
use Classes\Element;
use Classes\Fetch;
use Classes\Helper;
use Desarrolla2\Cache;
use Faker\Factory;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class Torano
{

    /**
     * @var null|Cache\AbstractCache
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
        try {
            $this->currentProxy = $this->getFreeProxy();
        }catch(ToranoException $e){
            echo $e->getMessage();exit;
        }

    }



    public function getData()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_CONNECTTIMEOUT => $this->currentProxy->timeout ?? 5,
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_PROXY => "socks5://" . $this->currentProxy->getUrl(),
            CURLOPT_URL => $this->fetch->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->config->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $this->fetch->getData(),

        ]);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($this->fetch->getMethod()));

        if(is_array($this->fetch->getHeaders()) && count($this->fetch->getHeaders())>0){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->fetch->getHeaders());
        }
        $output = curl_exec($curl);
        if (curl_error($curl)) {
            $this->error = curl_error($curl);
            $this->success = false;
            return null;
        }
        $this->lastInfos = curl_getinfo($curl);
        curl_close($curl);
        if(empty($output)){
            $this->success = false;
            return null;
        }
        $this->success = true;
        return $output;



    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function Old_getData()
    {

        $curl = curl_init($this->fetch->getUrl());
        curl_setopt($curl,CURLOPT_PROXYTYPE,CURLPROXY_SOCKS5);
        curl_setopt($curl, CURLOPT_PROXY, "socks5://".$this->currentProxy->getUrl());
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->config->timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$this->config->timeout);




        if(is_array($this->fetch->getHeaders()) && isset($this->fetch->getHeaders()["User-Agent"])){
            $userAgent = $this->fetch->getHeaders()["User-Agent"];
        }else{
            $faker = Factory::create();
            $userAgent = $faker->userAgent;
        }

        if(is_array($this->fetch->getHeaders()) && !isset($this->fetch->getHeaders()["Accept-Encoding"])) {
            $this->fetch->addHeader('Accept-Encoding','identity');
        }
        if(is_array($this->fetch->getHeaders()) && !isset($this->fetch->getHeaders()["Upgrade-Insecure-Requests"])) {
            $this->fetch->addHeader('Upgrade-Insecure-Requests',1);
        }
        if(is_array($this->fetch->getHeaders()) && !isset($this->fetch->getHeaders()["Connection"])) {
            $this->fetch->addHeader('Connection','keep-alive');
        }
        if(is_array($this->fetch->getHeaders()) && !isset($this->fetch->getHeaders()["Accept-Language"])) {
            $this->fetch->addHeader('Accept-Language','de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7');
        }


        $headers = Fetch::buildHeaders($this->fetch->getHeaders());

        if(!empty($this->fetch->getContentType())){
            $headers[] = "Content-Type: ".$this->fetch->getContentType();
        }

        if(count($headers) !== 0) {
            curl_setopt_array($curl, [CURLOPT_HTTPHEADER => $headers]);
        }
        if(strtoupper($this->fetch->getMethod())!=='GET'){
            curl_setopt($curl,CURLOPT_CUSTOMREQUEST,strtoupper($this->fetch->getMethod()));
        }
        if($this->fetch->getData() !== null){
            curl_setopt($curl,CURLOPT_POSTFIELDS,$this->fetch->getData());
        }

        curl_setopt($curl,CURLOPT_USERAGENT,$userAgent);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $output=curl_exec($curl);


        if (curl_error($curl)) {
            $this->error = curl_error($curl);

            $output = false;
            $this->success = false;
        }
        $this->lastInfos = curl_getinfo($curl);
        curl_close($curl);
        if(empty($output)){
            return null;
        }
        $this->success = true;
        return $output;
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
        $proxy->setStatus(Element::STATUS_WARNING);
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
        if(!is_array($proxys)){
            throw new ToranoException("Torano is not initialized",ToranoException::NOT_INITIALIZED);
        }
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
