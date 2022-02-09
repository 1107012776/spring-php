<?php

namespace SpringPHP\Request;

use SpringPHP\Inter\RequestInter;

class RequestHttp implements RequestInter
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getUri()
    {
        return $this->request->server['request_uri'];
    }


}