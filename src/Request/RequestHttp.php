<?php

namespace SpringPHP\Request;

use SpringPHP\Inter\RequestInter;

class RequestHttp implements RequestInter
{
    protected $request;
    protected $serv = null;

    public function __construct($request, $serv = null)
    {
        $this->request = $request;
        $this->serv = $serv;
    }

    public function getUri()
    {
        return $this->request->server['request_uri'];
    }

    /**
     * @return \Swoole\Server
     */
    public function getServer()
    {
        return $this->serv;
    }

}