<?php

namespace App\Controller;

use SpringPHP\Core\RestController;
use SpringPHP\Template\Render;

class Resource extends RestController
{
    public function index()
    {
        return Render::getInstance()->render("Index/index", [
            'msg' => 13123123
        ]);
    }

    public function view()
    {

        return ['msg' => $this->request->get('id', 0)];
    }

    public function create()
    {

        return ['msg' => $this->request->post('id', 0)];
    }

    public function update()
    {
        return [
            'id' => $this->request->post('id', 0),
            'rawContent' => $this->request->rawContent(),
            'getContent' => $this->request->getContent(),
            'getData' => $this->request->getData(),
        ];
    }


    public function delete()
    {
        return [
            'id' => $this->request->post('id', 0),
            'rawContent' => $this->request->rawContent(),
            'getContent' => $this->request->getContent(),
            'getData' => $this->request->getData(),
        ];
    }

}