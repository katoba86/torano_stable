<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 04.06.2019
 * Time: 09:10
 */

namespace Classes\Vpn;


class VpnConfig
{

    const USER = 'kai.bartholome@outlook.com';
    const PASS = 'vTq5eKEjYo4T9Z';
    const DEFAULT_NUM = 5;
    const VPN_END_ID = 600;
    const VPN_STARTING_ID = 1;


    const CACHE_VPN_CONNECTION_KEY = "VPN_CONNECTIONS";
    const CACHE_VPN_ALL_HOSTS = "ALL_VPN_HOSTS";
    const VPN_LIFETIME = 60*60*24*365;
    const NUM_TRIES_TO_CONNECT = 1;
    const COUNTRY = 'de';
    const SERVER_FETCH_URL = 'https://nordvpn.com/wp-admin/admin-ajax.php?action=servers_recommendations';

}
