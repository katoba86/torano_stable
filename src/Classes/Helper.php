<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 18.02.2017
 * Time: 13:20
 */

namespace Classes;


use Classes\Torano\Torano;
use Classes\Torano\TorConfig;
use Classes\Torano\TorElement;
use Classes\Vpn\VpnConfig;

use Desarrolla2\Cache\Predis as PredisCache;
use Predis\Client as PredisClient;
use Desarrolla2\Cache\File as FileCache;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;


class Helper
{


    /**#
     * @param Element[]|TorElement[] $proxyArray
     * @param Element[]|TorElement[]|null $failureArray
     * @param OutputInterface $output
     */
    public static function displayToranos(OutputInterface $output,array $proxyArray,array $failureArray = null)
    {

        $table = new Table($output);
        $table->setHeaders(["IP","Port","Status","lastChecked","calls","errors","PID","Country","Latency","Msg"]);
        foreach($proxyArray as $element){

            $row = [
                $element->ip,
                $element->port,
                $element->getStatusAsString(),
                ($element->lastChecked!==0)?date("d.m.Y H:i:s",$element->lastChecked):"-",
                $element->getNumSuccess(),
                $element->getNumFailed(),
                $element->pid,
                $element->getCountry(),
                ($element->latency!==null)?$element->latency."ms":"-",
                $element->getLastError(),

            ];
            $table->addRow($row);
        }
        if($failureArray!==null) {
            $table->addRow(new TableSeparator());
            foreach ($failureArray as $element) {
                $row = [
                    $element->ip,
                    $element->port,
                    $element->getStatusAsString(),
                    ($element->lastChecked!==0)?date("d.m.Y H:i:s",$element->lastChecked):"-",
                    $element->getNumSuccess(),
                    $element->getNumFailed(),
                    $element->pid,
                    $element->getCountry(),
                    ($element->latency !== null) ? $element->latency . "ms" : "-",
                    $element->getLastError(),

                ];
                $table->addRow($row);
            }
        }
        $table->render();

    }

    /**
     * @return array|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getVpnArray():array
    {
        $cached = (Helper::getCache())->get(VpnConfig::CACHE_VPN_CONNECTION_KEY);
        return (!is_array($cached))?[]:$cached;
    }

    /**
     * @return array|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getToranoArray():array
    {
        $cached = (Helper::getCache())->get(TorConfig::SAVE_ARRAY);
        return (!is_array($cached))?[]:$cached;
    }

    /**
     * @param OutputInterface $output
     * @param $array
     * @return bool
     */
    public static function displayVpn(OutputInterface $output,$array)
    {
        if(!is_array($array)){
            return false;
        }

        $table = new Table($output);
        $table->setHeaders(["Identifer","Status","lastChecked","calls","errors","Latency","Country","LastError"]);
        foreach($array as $element){

            $row = [
                $element->getName(),
                $element->getStatusAsString(),
                ($element->lastChecked!==0)?date("d.m.Y H:i:s",$element->lastChecked):"-",
                $element->getNumSuccess(),
                $element->numFailed,
                ($element->latency!==null)?$element->latency."ms":"-",
                $element->getCountry(),
                $element->getLastError()


            ];
            $table->addRow($row);
        }


        $table->render();

    }


    /**
     * @return Predis|FileCache
     * @throws \RuntimeException
     */
    public static function getCache(){


        $cacheName = getenv("cache");
       if($cacheName === 'redis'){

           $client = new PredisClient('tcp://localhost:6379');
           $adapter = new PredisCache($client);


       }elseif($cacheName === 'file'){
           $cacheDir = '/tmp';
           $adapter = new FileCache($cacheDir);
           $adapter->withOption('ttl', 3600*24*30*365);
       }else{
           throw new \RuntimeException("Cache method\t".$cacheName."\t not implemented yet. Do this in this file!");
       }


        return $adapter;
    }

    /**
     * @param array $headers
     * @return string
     */
    public static function buildHeadersForWget(array $headers):string
    {
        if(is_array($headers) && count($headers)>=1){

            $oldHeaders=$headers;
            $headers=[];
            foreach($oldHeaders as $key=>$value){
                $headers[]="--header '".$key.": ".$value."'";
            }
            return implode(" ",$headers);
        }
        return "";
    }



    public static function buildHeaders(array $headers):array
    {
        if(is_array($headers) && count($headers)>=1){

            $oldHeaders=$headers;
            $headers=[];
            foreach($oldHeaders as $key=>$value){
                $headers[]=$key.": ".$value;
            }
            return $headers;
        }
        return [];
    }

    /**
     * @param $port
     * @return bool|int
     */
    public static function getPidForPort($port){
        $command = "sudo ss -pln  |grep tor | grep ".$port;
        $output = exec($command);
        preg_match("/,pid=(\d{2,}),/i",$output,$matches);
        if(count($matches) === 2){
            return (int)$matches[1];
        }
        return false;
    }


    public static function updateLatencyAndIp(TorElement &$torElement){





        try {
            $torInstance = new Torano();
            $config = new Config();
            $config->type = Config::TYPE_TOR;
            $fetch = new Fetch();
            $fetch->setUrl(self::getControlUrl());
            $fetch->setMethod("GET");
            $torInstance->init($config,$fetch);
            $torData = $torInstance->getData();



            $torData = json_decode($torData,false);

            if(!is_object($torData) || !isset($torData->ip)){
                throw new RuntimeException("Cant optain ip");
            }



            $torElement->setIp($torData->ip);
            if(isset($torData->country_code)) {
                $torElement->setCountry($torData->country_code);
            }




        }catch(\Exception $e){

            $torElement->setStatus(TorElement::STATUS_ERR);
            $torElement->ip = null;
            $torElement->numFailed+=1;
            $torElement->setLastError("NO IP Fetched");
            return;
        }

        try {
            $ping = new Ping($torElement->getIp());
            $latency = $ping->ping();
            $torElement->setLatency($latency);
        }catch(\Exception $e){
            $torElement->setStatus(TorElement::STATUS_ERR);
            $torElement->setLatency(null);
            $torElement->setLastError("Cant detect Latency");
            $torElement->numFailed+=1;
        }

    }

    /**
     * @param string $res
     * @return false|string
     */
    public static function decodeIfEncoded(string $res)
    {
        $is_gzip = 0 === mb_strpos($res , "\x1f" . "\x8b" . "\x08");

        if((bool)$is_gzip===true){
            return gzdecode($res);
        }

        return $res;
    }

    private static function getControlUrl()
    {
        return  getenv("ip")."/ip.php";
    }


}
