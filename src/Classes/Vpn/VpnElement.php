<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 04.06.2019
 * Time: 09:35
 */

namespace Classes\Vpn;


use Classes\Element;
use Classes\Ping;
use Classes\Torano\ToranoConnection;
use Classes\Torano\TorConfig;

class VpnElement extends Element
{


    public $hostname;

    public $provider;


    public function selfTest()
    {
        try {
            $vpn = new Vpn($this);
            $vpnData = $vpn->setTimeout(3)->getWithCurl(TorConfig::getControlUrl(), $this->getHostname());

            $vpn->setProxySuccess($this,null);


            $vpnData = json_decode($vpnData,false);
            if(!is_object($vpnData) || !isset($vpnData->ip)){
                throw new \RuntimeException("Cant optain ip");
            }
            $this->setIp($vpnData->ip);

            if(isset($torData->country_code)) {
                $this->setCountry($vpnData->country_code);
            }




        }catch(\Exception $e){

            $this->setStatus(self::STATUS_ERR);

            $this->numFailed+=1;
            $this->setLastError("NO IP Fetched");
            return;
        }





    }


    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider): void
    {
        $this->provider = $provider;
    }



    public function getName()
    {
        return $this->hostname;
    }

    /**
     * @return mixed
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param mixed $hostname
     */
    public function setHostname($hostname): void
    {
        $this->hostname = $hostname;
    }



}
