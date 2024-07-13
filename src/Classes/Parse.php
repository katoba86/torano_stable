<?php

namespace Classes;

use League\Config\Configuration;
use Nette\Schema\Expect;
use Nette\Schema\ValidationException;

class Parse
{

    protected Configuration $config;

    public function __construct(public array $input){
        $this->initConfig();
        $this->parseInput();
    }


    public function initConfig():void{
        $this->config = new Configuration([
            'fetch'=>Expect::structure([
                'url'=>Expect::string()->required(),
                'method'=>Expect::anyOf('GET','POST','HEAD')->default('GET'),
                'headers'=>Expect::array()->nullable()->default([]),
                'data'=>Expect::string(),
            ]),
            'config'=>Expect::structure([
                'timeout'=>Expect::int(20),
                'retry'=>Expect::int(1),

            ])
        ]);
    }

    public function parseInput():void{

            $this->config->merge($this->input);

    }

    public function getFetch():Fetch{
        $fetch = new Fetch();
        $fetch->setHeaders($this->get('fetch.headers'));
        $fetch->setMethod($this->get('fetch.method'));
        $fetch->setUrl($this->get('fetch.url'));
        $fetch->setData($this->get('fetch.data'));
        return $fetch;
    }

    public function getConfig():Config{
        $config = new Config();
        $config->timeout = $this->get('config.timeout');
        $config->retry = $this->get('config.retry');
        return $config;
    }

    public function get(string $key):mixed{
        return $this->config->get($key);
    }
}
