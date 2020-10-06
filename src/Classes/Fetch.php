<?php
/**
 * Created by PhpStorm.
 * User: kaiba
 * Date: 07.06.2019
 * Time: 10:57
 */

namespace Classes;


class Fetch
{
    private $method = "GET";

    private $contentType = "";

    private $url = "";

    private $data = null;

    private $headers = [];

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
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
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     * @return Fetch
     */
    public function setContentType(string $contentType): Fetch
    {
        $this->contentType = $contentType;
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
    public function setData($data)
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
        $this->headers = $headers;
        return $this;
    }



}
