<?php
/**
 * Created by PhpStorm.
 * User: katoba
 * Date: 11.06.16
 * Time: 12:59
 */

namespace Classes\Vpn;


use Classes\AutoDetect;
use Classes\Config;
use Classes\ConnectorInterface;
use Classes\Fetch;
use Classes\Helper;
use Desarrolla2\Cache\Cache;
use Faker\Factory;

class Vpn extends VpnConfig implements ConnectorInterface
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
     * @var VpnElement[]
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
     * @var VpnElement
     */
    public $currentProxy;
    /**
     * @var array
     */
    private $lastInfos = [];


    public function init(Config $config,Fetch $fetch):void
    {

        $this->config = $config;
        $this->fetch = $fetch;
        try {
            $this->cache = Helper::getCache();
        } catch (\RuntimeException $e) {
            echo "Error: No Cache enabled!";exit;
        }



        $knownVpns = $this->cache->get(VpnConfig::CACHE_VPN_CONNECTION_KEY);

        if(null === $knownVpns || false == $knownVpns){
            $knownVpns = [];
        }
        $element = $this->getVpnElement($knownVpns);
        if($element instanceof VpnElement){
            $this->currentProxy = $element;
            $this->proxys = $knownVpns;
            return;
        }

        $allHosts =  $this->cache->get(VpnConfig::CACHE_VPN_ALL_HOSTS);
        if(!isset($allHosts[strtoupper($this->config->country)])){

            AutoDetect::importResources();
            $allHosts =  $this->cache->get(VpnConfig::CACHE_VPN_ALL_HOSTS);
            if(!isset($allHosts[strtoupper($this->config->country)])){
                echo "<pre>";print_r("Error: No Servers for country ".$this->config->country." known");exit;
            }



        }

        $hostname = $this->getVpnElementForList($allHosts[strtoupper($this->config->country)]);
        if(null === $hostname){
            echo "<pre>";print_r("Error: no proxy found");exit;
        }

       $this->currentProxy = new VpnElement();
        $this->currentProxy->country = strtoupper($this->config->country);
        $this->currentProxy->hostname = $hostname;
        $this->currentProxy->setCreated(time());

        $knownVpns[] = $this->currentProxy;
        $this->cache->set(VpnConfig::CACHE_VPN_CONNECTION_KEY,$knownVpns,VpnConfig::VPN_LIFETIME);
            $this->proxys = $knownVpns;


    }

    /**
     * @param VpnElement[] $proxys
     * @return VpnElement|null
     */
    private function getVpnElement(array $proxys):?VpnElement{



        $list = [];
        foreach($proxys as $index=>$proxy){
            /*@var $proxy TorElement */
            if($proxy->getStatus()!==VpnElement::STATUS_ERR) {

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

    /**
     * @param string[] $proxyList
     * @return string
     */
    private function getVpnElementForList($proxyList)
    {
        //shuffle($proxyList);
        return array_pop($proxyList);
    }


    /**
     * Get mixed data from server
     *
     * @return mixed
     */
    public function getData()
    {



        $curl = curl_init($this->fetch->getUrl());


        curl_setopt($curl, CURLOPT_PROXY, $this->currentProxy->getHostname());
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($curl, CURLOPT_PROXYPORT, '1080');
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, VpnConfig::USER . ":" . VpnConfig::PASS);
        curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);


        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, "/tmp/coookies");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, true);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($curl, CURLOPT_TIMEOUT, $this->config->timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$this->config->timeout);

        if(is_array($this->fetch->getHeaders()) && isset($this->fetch->getHeaders()["User-Agent"])){
            $userAgent = $this->fetch->getHeaders()["User-Agent"];
        }else{
            $faker = Factory::create();
            $userAgent = $faker->userAgent;
        }

        $headers = Helper::buildHeaders($this->fetch->getHeaders());

        if($this->fetch->getContentType() !== null){
            $headers[] = "content-type: ".$this->fetch->getContentType();
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
        var_dump($output);exit;

        if (curl_error($curl)) {
            $this->error = curl_error($curl);

            $output = false;
            $this->success = false;
        }
        $this->lastInfos = curl_getinfo($curl);
        curl_close($curl);
        if(false === $output || empty($output)){
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


    public function setProxyFailed(VpnElement $proxy,$latency,$msg = null){

        ///$proxy->setNumCalled($proxy->getNumCalled()+1);
        $proxy->setNumFailed($proxy->getNumFailed()+1);
        $proxy->setStatus(VpnElement::STATUS_WARNING);
        if($msg!==null){
            $proxy->setLastError($msg);
        }
        $proxy->setLatency($latency);
        foreach($this->proxys as $index=>$_p){
            if($_p->getHostname() === $proxy->getHostname()){
                $this->proxys[$index] = $proxy;
            }
        }
        $this->cache->set(VpnConfig::CACHE_VPN_CONNECTION_KEY,$this->proxys,VpnConfig::VPN_LIFETIME);
    }


    public function setProxySuccess(VpnElement $proxy,$latency){

        $proxy->setNumSuccess($proxy->getNumSuccess()+1);
        $proxy->setStatus(VpnElement::STATUS_OK);
        $proxy->setLatency($latency);
        foreach($this->proxys as $index=>$_p){
            if($_p->getHostname() === $proxy->getHostname()){
                $this->proxys[$index] = $proxy;
            }
        }
        $this->cache->set(VpnConfig::CACHE_VPN_CONNECTION_KEY,$this->proxys,VpnConfig::VPN_LIFETIME);
    }

}
