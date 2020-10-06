<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 07.06.2019
 * Time: 10:13
 */

namespace Classes;


use Classes\Torano\ToranoConnection;
use Classes\Vpn\Vpn;

class Connection
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var Fetch
     */
    private $fetch;




    public function __construct(Config $config,Fetch $fetch)
    {
        $this->config = $config;
        $this->fetch = $fetch;
    }

    private function init()
    {
        if($this->config->type === Config::TYPE_AUTO){

            if(preg_match("/meinfernbus|flixbus/i",$this->fetch->getUrl())){
                $this->config->type = Config::TYPE_VPN;
            }else {
                $this->config->type = Config::TYPE_DEFAULT;
            }
        }

        switch($this->config->type){
            case Config::TYPE_VPN:
                $this->connector = new Vpn();
                break;
            case Config::TYPE_TOR:
                $this->connector = new Torano\Torano();
                break;
            default:
                $this->connector = new Vpn();
        }


    }


    public function run()
    {
        $this->init();


        $this->connector->init($this->config,$this->fetch);
        $data = $this->connector->getData();
        $this->connector->finish();
        return $data;

    }




    /**
     * @param Config $config
     * @return Connection
     */
    public function setConfig(Config $config): Connection
    {
        $this->config = $config;
        return $this;
    }















}
