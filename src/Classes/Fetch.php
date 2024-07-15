<?php

namespace Classes;


class Fetch
{
    private string $method = "GET";


    private string $url = "";

    private $data = null;

    private array $headers = [];

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

     public function parse(string $input){

     }

    /**
     * @param string $method
     * @return Fetch
     */
    public function setMethod(string $method): Fetch
    {
        $this->method = $method;
        return $this;
    }


    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Fetch
     */
    public function setUrl(string $url): Fetch
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     * @return Fetch
     */
    public function setData($data):Fetch
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Fetch
     */
    public function setHeaders(array $headers): Fetch
    {
        if(!array_is_list($headers)){
            $this->headers = [];
            foreach ($headers as $key => $v) {
                $this->headers[] = $key . ": " . $v;
            }
            return $this;
        }
        $this->headers = $headers;
        return $this;
    }


    public function getCacheKey():string
    {
        $h = $this->headers;
        sort($h);
        $h = md5(json_encode($h));
        return md5(implode("_",[
            $this->method,
            $this->url,
            $h,
            $this->data
        ]));


    }


}
