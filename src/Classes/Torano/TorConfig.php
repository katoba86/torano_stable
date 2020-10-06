<?php

namespace Classes\Torano;


class TorConfig
{
    const SAVE_ARRAY = "TORANO_PROXYS";



    const NUM_TRIES_TO_CONNECT = 1;

    const WORKING_DIR = "/tmp/torano";
    const TOR_CFG = "cfg/";
    const TOR_DATA = "data/";
    const TOR_SERVER_DIR = "data_:id";
    const TOR_CONFIG_NAME = "torcfg:id";


    const SAVE_TIME = 60*60*24*7;

    const STARTING_SOCKS = 9050;
    const STARTING_SOCKS_CONTROL = 9060;
    const STARTING_DELEGATE = 9700;


    const DELEGATE_PARAMS = " -P:p SERVER=http SOCKS=localhost::s";
    const TOR_CALL = "tor";
    const TOR_PARAMS = " -f :config";

    const MIN_LATENCY = 250;
    const FINISH_STRING = "finished";

    const MEMCACHE_SERVER = "localhost";
    const MEMCACHE_PORT = 11211;
    const DEFAULT_NUM_SERVERS = 6;


    public static function getControlIp(){
        return getenv("ip");
    }
    public static function getControlUrl(){
        return "http://".self::getControlIp()."/ip.php";
    }


    public static $TOR_CONFIG_TEMPLATE = [
        "RunAsDaemon 1",
        //"ExitNodes {:country}",
        //"StrictNodes 1",
        "ControlPort :control",
        "SocksPort :socks",
       "DataDirectory :datadir"
    ];



}
