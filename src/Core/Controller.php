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
     * @var \SpringPHP\Inter\ResponseInter
     */
    protected $response;


    public function init(RequestInter $request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function beforeAction($action = '')
    {
        return true;
    }

    public function responseCode($code = 200)
    {
        !empty($this->response) && $this->response->setStatusCode($code);
    }
}