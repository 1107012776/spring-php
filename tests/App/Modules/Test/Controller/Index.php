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