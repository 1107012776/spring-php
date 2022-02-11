<?php

namespace App\Controller;

use SpringPHP\Core\Controller;

class Error extends Controller
{
    public function index404()
    {
        $this->responseCode(404);
        return 'Error404不存在该页面';
    }
}