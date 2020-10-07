<?php

namespace Classes;


class Ping {

    private $host;
    private $ttl;
    private $timeout;
    private $port = 80;
    private $data = 'Ping';
    private $commandOutput;

    /**
     * Called when the Ping object is created.
     *
     * @param string $host
     *   The host to be pinged.
     * @param int $ttl
     *   Time-to-live (TTL) (You may get a 'Time to live exceeded' error if this
     *   value is set too low. The TTL value indicates the scope or range in which
     *   a packet may be forwarded. By convention:
     *     - 0 = same host
     *     - 1 = same subnet
     *     - 32 = same site
     *     - 64 = same region
     *     - 128 = same continent
     *     - 255 = unrestricted
     * @param int $timeout
     *   Timeout (in seconds) used for ping and fsockopen().
     * @throws \Exception if the host is not set.
     */
    public function __construct($host, $ttl = 255, $timeout = 10) {
        if (!isset($host)) {
            throw new \Exception("Error: Host name not supplied.");
        }

        $this->host = $host;
        $this->ttl = $ttl;
        $this->timeout = $timeout;
    }

    /**
     * Set the ttl (in hops).
     *
     * @param int $ttl
     *   TTL in hops.
     */
    public function setTtl($ttl) {
        $this->ttl = $ttl;
    }

    /**
     * Get the ttl.
     *
     * @return int
     *   The current ttl for Ping.
     */
    public function getTtl() {
        return $this->ttl;
    }

    /**
     * Set the timeout.
     *
     * @param int $timeout
     *   Time to wait in seconds.
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    /**
     * Get the timeout.
     *
     * @return int
     *   Current timeout for Ping.
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Set the host.
     *
     * @param string $host
     *   Host name or IP address.
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Get the host.
     *
     * @return string
     *   The current hostname for Ping.
     */
    public function getHost() {
        return $this->host;
    }


    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * Get the port (only used for fsockopen method).
     *
     * @return int
     *   The port used by fsockopen pings.
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Return the command output when method=exec.
     * @return string
     */
    public function getCommandOutput(){
        return $this->commandOutput;
    }

    /**
     * Matches an IP on command output and returns.
     * @return string
     */
    public function getIpAddress() {
        $out = array();
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $this->commandOutput, $out)){
            return $out[0];
        }
        return null;
    }


    public function ping() {
        return $this->pingExec();;
    }


    private function pingExec() {

        $host = escapeshellcmd($this->host);


            $exec_string = ' ping -c 4 '.$host.' | tail -1| awk \'{print $4}\' | cut -d \'/\' -f 2';


        exec($exec_string, $output, $return);
        if(is_array($output) && count($output)===1 && strpos($output[0], $host) === false){
        return round((float)$output[0]);
        }else{
            return null;
        }
    }


}