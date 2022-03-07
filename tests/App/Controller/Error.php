<?php

namespace App\Controller;


use SpringPHP\Core\HttpController;

class Error extends HttpController
{
    public function index404()
    {
        $this->responseCode(404);
        return 'Error404不存在该页面';
    }
}