<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 07.06.2019
 * Time: 10:06
 */

namespace Classes;


class Config
{

    const TYPE_DEFAULT = 2;

    const TYPE_AUTO = 0;
    const TYPE_VPN = 1;
    const TYPE_TOR = 2;


    public $type = self::TYPE_AUTO;

    public $country = 'de';
    public $provider = 'auto';
    public $retry = 2;
    public $timeout = 20;




}
