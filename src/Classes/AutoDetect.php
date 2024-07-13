<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 15.06.2018
 * Time: 12:21
 */

namespace Classes;


use Classes\Torano\TorConfig;

use Desarrolla2\Cache\Adapter\Predis;

use Symfony\Component\Process\Process;

class AutoDetect
{


    public static function killAll()
    {
        $cache = Helper::getCache();

        $cache->set(TorConfig::SAVE_ARRAY,[]);


        (Process::fromShellCommandline(Helper::addSudo()." killall " . TorConfig::TOR_CALL))->run();
        (Process::fromShellCommandline(Helper::addSudo()."rm -rf " . TorConfig::WORKING_DIR))->run();
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
        $ret =  trim(shell_exec($cmd));
        if(empty($ret)){
            return "127.0.0.1";
        }else{
            return $ret;
        }
    }





    public function run(){

        $values=[];
        if(getenv('ip')===null || !preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', getenv('ip'))) {
            $values["ip"] = $this->getMyIP();
        }else{
            self::output(false,"IP is already set:\t".getenv('ip'));
        }
        echo "\n\n";
        if($this->checkTorIsInstalled()){
            self::output(false,"Tor is installed!");
        }else{
            self::output("true","Tor is not installed.");exit;
        }

        $ip = (isset($values["ip"]))?$values["ip"]:getenv('ip');
        if($this->checkControlIp($ip)){
            self::output(false,"Control-IP is reachable and installed");
        }else{
            self::output(true,"Control-IP is not reachable or not configured correctly");exit;
        }
        $path = realpath(__DIR__."/../../");
        self::output(false,"Store values to \t".$path);
        if(getenv('cache')!==null) {
            $values["cache"] = $this->getCache();
        }else{
            self::output(false,"Cache is already set to:\t".getenv('cache'));
        }

        foreach ($values as $key => $value) {
            file_put_contents($path."/.env","\n".$key."=\"".trim($value)."\"\n",FILE_APPEND);
        }





    }

    private function checkRedis(int $redisPort=6379):bool
    {
        $command1 = `netstat -antpl  |grep tcp | grep $redisPort`;
        if(null === $command1){return false;}
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


    public static function output(bool $error = false,string  $message=""):void
    {
        echo ($error)?"\xE2\x9C\x98":"\xE2\x9C\x94"."\t".$message."\n";
    }


    private function checkControlIp(string $ip):bool
    {

        try {
            $content = file_get_contents("http://".$ip."/ip.php");
            $test = json_decode($content, true);

            unset($test);unset($content);
        }catch(\Exception $e){
            return false;
        }
        return true;
    }
}
