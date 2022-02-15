<?php

namespace App\Controller;

use App\Task\HelloWordTask;

use SpringPHP\Core\Controller;
use SpringPHP\Template\Render;
use Swoole\Coroutine;

class Index extends Controller
{
    public function index()
    {
        return Render::getInstance()->render("Index/index", [
            'msg' => 13123123
        ]);
    }

    public function haha()
    {
        //投递异步任务
        /*     $task_id = $this->request->getServer()->task(serialize(new HelloWordTask([
                 'msg' => 'HelloWordTask'
             ])));*/
        $task_id = $this->request->getServer()->task(new HelloWordTask([
            'msg' => 'HelloWordTask'
        ]));
        return ['msg' => 'hello12', 'task_id' => $task_id];
    }


    public function restart()
    {
        Coroutine::create(function () {
            Render::getInstance()->restartWorker();
        });
        return ['msg' => 'restart render'];
    }
}
