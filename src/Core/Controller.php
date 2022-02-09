<?php

namespace SpringPHP\Core;

use SpringPHP\Inter\RequestInter;

abstract class Controller
{
    /**
     * @var RequestInter
     */
    protected $request;
    /**
     * @var \Swoole\Http\Response
     */
    protected $response;

    public function __construct(RequestInter $request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}