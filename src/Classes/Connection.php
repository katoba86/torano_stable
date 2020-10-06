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

        $this->config->type = Config::TYPE_TOR;
        $this->connector = new Torano\Torano();



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
