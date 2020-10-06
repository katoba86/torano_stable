<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 07.06.2019
 * Time: 10:55
 */

namespace Classes;


interface ConnectorInterface
{


    /**
     * Init the Connector
     * @param Config $config
     * @param Fetch $fetch
     */
    public function init(Config $config,Fetch $fetch):void;

    /**
     * Get mixed data from server
     *
     * @return mixed
     */
    public function getData();

    /**
     * Cleaning up or proxy set
     */
    public function finish():void;

}
