<?php

namespace App\Controller;

use SpringPHP\Core\Controller;
use SpringPHP\Template\Render;

class Index extends Controller
{
    public function index()
    {
        return 'sadasdsadsad'.Render::getInstance()->render("Index/index",[
            'msg' => 13123123
            ]);
    }

    public function haha()
    {
        return ['msg' => 'hello12'];
    }
}