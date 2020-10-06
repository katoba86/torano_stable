<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 05.06.2019
 * Time: 09:00
 */

namespace Classes;

/**
 * Class Element
 * @property int $pid
 * @method setPid(int $pid)
 * @package Classes
 */
abstract class Element
{



    const STATUS_UNCHECKED  = 0;
    const STATUS_OK = 1;
    const STATUS_ERR = 2;
    const STATUS_WARNING = 3;


    public $name = "";
    public $ip = "";
    public $lastChecked = 0;
    public $created = 0;
    public $status = self::STATUS_UNCHECKED;
    public $latency;

    public $numSuccess = 0;
    public $numFailed = 0;

    public $lastError = "";

    public $country = "";
    public $lastConnectTimes = [];

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return Element
     */
    public function setIp(string $ip): Element
    {
        $this->ip = $ip;
        return $this;
    }




    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return Element
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }


    public function getStatusAsString()
    {

        switch($this->getStatus()){
            case self::STATUS_ERR:
                return "ERR";
            case self::STATUS_OK:
                return "OK";
            case self::STATUS_UNCHECKED:
                return "UNCHECKED";
            case self::STATUS_WARNING:
                return "WARNING";
            default:
                return "?";
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Element
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }




    /**
     * @param mixed $lastError
     * @return Element
     */
    public function setLastError($lastError)
    {
        $this->lastError = $lastError;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatency()
    {
        return $this->latency;
    }

    /**
     * @param mixed $latency
     * @return Element
     */
    public function setLatency($latency)
    {
        $this->latency = $latency;
        return $this;
    }




    /**
     * @return mixed
     */
    public function getLastChecked()
    {
        return $this->lastChecked;
    }

    /**
     * @param mixed $lastChecked
     * @return Element
     */
    public function setLastChecked($lastChecked)
    {
        $this->lastChecked = $lastChecked;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     * @return Element
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Element
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumSuccess()
    {
        return $this->numSuccess;
    }

    /**
     * @param mixed $numSuccess
     * @return Element
     */
    public function setNumSuccess($numSuccess)
    {
        $this->numSuccess = $numSuccess;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumFailed()
    {
        return $this->numFailed;
    }

    /**
     * @param mixed $numFailed
     * @return Element
     */
    public function setNumFailed($numFailed)
    {
        $this->numFailed = $numFailed;
        return $this;
    }

    /**
     * @return array
     */
    public function getLastConnectTimes(): array
    {
        return $this->lastConnectTimes;
    }

    /**
     * @param array $lastConnectTimes
     * @return Element
     */
    public function setLastConnectTimes(array $lastConnectTimes): Element
    {
        $this->lastConnectTimes = $lastConnectTimes;
        return $this;
    }





}
