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
            'msg' => 13123123 . ' id=' . $this->request->params('id', 'null')
        ]);
    }

    public function haha()
    {
        //投递异步任务
        /*     $task_id = $this->request->getServer()->task(serialize(new HelloWordTask([
                 'msg' => 'HelloWordTask'
             ])));*/
        $task_id = $this->request->managerServer()->task(new HelloWordTask([
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

    public function getContent()
    {

        return ['msg' => $this->request->getContent()];
    }

    public function rawContent()
    {
        return ['msg' => $this->request->rawContent()];
    }

    public function get()
    {
        return ['msg' => $this->request->get('id', 0)];
    }

    public function post()
    {

        return ['msg' => $this->request->post('id', 0)];
    }

    public function put()
    {
        return [
            'id' => $this->request->post('id', 0),
            'rawContent' => $this->request->rawContent(),
            'getContent' => $this->request->getContent(),
            'getData' => $this->request->getData(),
        ];
    }


    public function head()
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