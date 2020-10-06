<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 15.06.2018
 * Time: 12:21
 */

namespace Classes;


use Classes\Torano\TorConfig;
use Classes\Vpn\VpnConfig;
use Desarrolla2\Cache\Adapter\Predis;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;

class AutoDetect
{


    public static function killAll()
    {
        $cache = Helper::getCache();
        $cache->set(VpnConfig::CACHE_VPN_CONNECTION_KEY,[]);
        $cache->set(TorConfig::SAVE_ARRAY,[]);
        $cache->set(VpnConfig::CACHE_VPN_ALL_HOSTS,[]);

        (Process::fromShellCommandline("killall " . TorConfig::TOR_CALL))->run();
        (Process::fromShellCommandline("rm -rf " . TorConfig::WORKING_DIR))->run();
        self::output(false,"Clearing all Caches");
        self::output(false,"Kill all Tor instances");

    }

    private function checkTorIsInstalled()
    {
        $exec = `tor --version`;
        return (strpos(strtolower($exec), 'tor version') !== false);
    }


    public function getMyIP()
    {
        $cmd = 'dig +short myip.opendns.com @resolver1.opendns.com';
        return trim(shell_exec($cmd));
    }


    public static function importResources()
    {

        $cache = Helper::getCache();
        if(!isset($_SERVER["PWD"]) && isset($_SERVER["DOCUMENT_ROOT"])){
            $fileName = $_SERVER["DOCUMENT_ROOT"]."/res/servers.json";
            $noOutput = true;
        }else {
            $noOutput = false;
            $fileName = $_SERVER["PWD"] . "/res/servers.json";
        }
        $cmd = `cat $fileName | jq -c '.[] | "\(.hostname) \(.status)"'`;
        $cmd = explode("\n",$cmd);
        $servers = [];$n=0;
        foreach($cmd as $c){
            $c = explode(" ",str_replace("\"","",$c));
            if(!is_array($c) || count($c)!==2){continue;}
            if(strtolower(trim($c[1]))!=="online"){
                continue;
            }

            $matches = null;
            preg_match("#^([a-zA-Z]{2,3})\d{1,}.*?#",$c[0],$matches);

            if(count($matches)===2){
                if(!isset($servers[strtoupper($matches[1])])){
                    $servers[strtoupper($matches[1])]=[];
                }
                $servers[strtoupper($matches[1])][] = trim($c[0]);
                $n++;
            }
        }
        if(!$noOutput){
            self::output(false,"Got ".$n." Servers in ".count($servers)." countries");
        }
        $cache->set(VpnConfig::CACHE_VPN_ALL_HOSTS,$servers,VpnConfig::VPN_LIFETIME);


    }


    public function run(){

        $values=[];

        $values["ip"]=$this->getMyIP();


        echo "\n\n";
        if($this->checkTorIsInstalled()){
            self::output(false,"Tor is installed!");
        }else{
            self::output("true","Tor is not installed.");exit;
        }


        if($this->checkControlIp($values["ip"])){
            self::output(false,"Control-IP is reachable and installed");
        }else{
            self::output(true,"Control-IP is not reachable or not configured correctly");exit;
        }
        $path = realpath(__DIR__."/../../");
        self::output(false,"Store values to \t".$path);
        $values["cache"] = $this->getCache();

        foreach ($values as $key => $value) {
            file_put_contents($path."/.env",$key."=\"".trim($value)."\"\n",FILE_APPEND);
        }





    }

    private function checkRedis($redisPort=6379)
    {
        $command1 = `netstat -antpl  |grep tcp | grep $redisPort`;
        if(strpos(strtolower($command1), ':'.$redisPort) !== false){


            $command2= `redis-server --version`;
            return (strpos(strtolower($command2), 'redis server v=') !== false);

        }
        return false;
    }

    public function getCache(){


        //Check Redis
        if($this->checkRedis()){
            self::output(false,"Redis is installed. Using redis!");
            return "redis";
        }else{
            self::output(true,"Redis is not installed. Using file");
            return "file";
        }
    }


    public static function output($error = false,$message)
    {
        echo ($error)?"\xE2\x9C\x98":"\xE2\x9C\x94"."\t".$message."\n";
    }

    private function checkControlIp($ip)
    {

        try {
            $content = file_get_contents("http://".$ip."/ip.php");
            $test = json_decode($content, true);
            $test = $test["ip"] . $test["country_code"];
            unset($test);unset($content);
        }catch(\Exception $e){
            return false;
        }
        return true;
    }
}
