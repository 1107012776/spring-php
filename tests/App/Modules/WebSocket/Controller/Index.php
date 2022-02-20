<?php

namespace App\WebSocket\Controller;


use SpringPHP\Core\Controller;



class Index extends Controller
{
    public function index()
    {
        $this->responseCode(201);
        return [
            'data' => $this->request->getData(),
            'msg' => __CLASS__,
            'module_name' => $this->request->getModuleName()
        ];
    }


}