<?php

namespace App\Controller;

use SpringPHP\Core\Controller;
use SpringPHP\Template\Render;

class Index extends Controller
{
    public function index()
    {
        return 'sadasdsadsad' . Render::getInstance()->render("Index/index", [
                'msg' => 13123123
            ]);
    }

    public function haha()
    {
        //投递异步任务
        $task_id = $this->request->getServer()->task("task");
        return ['msg' => 'hello12', 'task_id' => $task_id];
    }
}