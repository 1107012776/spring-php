<?php

namespace App\Controller;

use App\Model\ServerManagerModel;
use App\Rpc\TestRpc;
use App\Rpc\ManagerRpc;
use App\Rpc\WebSocketTestRpc;
use App\Task\HelloWordTask;


use SpringPHP\Core\HttpController;
use SpringPHP\Template\Render;
use SpringPHP\WebSocket\WebSocketClient;
use Swoole\Coroutine;

class Index extends HttpController
{
    public function index()
    {
        return Render::getInstance()->render("Index/index", [
            'msg' => 13123123 . ' id=' . $this->request->params('id', 'null')
        ]);
    }

    public function test()
    {
        $model = new ServerManagerModel();
        return [
            'zhi' => $model->findAll()
        ];
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


    public function webSocketTest()
    {


        try {
            $ws = new WebSocketClient('ws://0.0.0.0:8397');
            var_dump($ws->ping());
            $ws->send('{"content":{"code":1,"content":{},"user_id":"static4025test"},"uri":"/Index/websocket"}');
            $frame = $ws->recv();
            $msg = "收到服务器响应数据：" . $frame->playload . PHP_EOL;
            var_dump($ws->close());
            return $msg;
        } catch (\Exception $e) {
            return "错误: " . $e->__toString();
        }

    }

    public function webSocketTest1()
    {
        $obj = new WebSocketTestRpc();
        $msg = $obj->Index_index([
            "code" => 1,
            "content" => "",
            "user_id" => "static4025test"
        ]);
        $msg .= $obj->Index_websocket([
            "code" => 3,
            "content" => "hahahahah",
            "user_id" => "static4025test"
        ]);
        return $msg;
    }

    public function webSocketTest2()
    {
        $obj = new ManagerRpc();
        $msg = '';
        $msg .= $obj->push([
            "code" => 3,
            "content" => "hahahahah" . date('Y-m-d H:i:s', time()),
            "user_id" => "static4025test"
        ]);
        return $msg;
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

    public function rpc()
    {
        $rpc = new TestRpc();
        $d = $rpc->Index_hello([
            'name' => '1'
        ]);
        $d .= $rpc->Index_hello([
            'name' => '2'
        ]);
        return $d;
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