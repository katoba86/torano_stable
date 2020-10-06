<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 18.02.2017
 * Time: 22:39
 */

namespace Classes\Torano;


use Classes\Element;

class TorElement extends Element
{




    public $url;

    public $port;
    public $pid;


    public function getName()
    {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return TorElement
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return TorElement
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     * @return TorElement
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
        return $this;
    }





}
