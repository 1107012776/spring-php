<?php

namespace App\Test\Controller;


use SpringPHP\Core\HttpController;
use SpringPHP\Template\Render;


class Index extends HttpController
{
    public function index()
    {
        return Render::getInstance()->render("Test/Index/index", [
            'port' => 8397,
        ]);
    }

    public function index2()
    {
        return Render::getInstance()->render("Test/Index/index", [
            'port' => 8398,
        ]);
    }


    public function index3()
    {
        return Render::getInstance()->render("Test/Index/index", [
            'port' => 8399,
        ]);
    }

    public function form()
    {
        return Render::getInstance()->render("Test/Index/form", [
            'msg' => __CLASS__,
            'module_name' => $this->request->getModuleName()
        ]);
    }

    public function upload()
    {
        return [
            'files' => var_export($this->request->files(), true),
            'tmpfiles' => var_export($this->request->tmpfiles(), true)
        ];
    }

}