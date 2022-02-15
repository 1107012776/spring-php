<?php

namespace App\Test\Controller;



use SpringPHP\Core\Controller;
use SpringPHP\Template\Render;


class Index extends Controller
{
    public function index()
    {
        return Render::getInstance()->render("Test/Index/index", [
            'msg' => __CLASS__,
            'module_name' => $this->request->getModuleName()
        ]);
    }


}