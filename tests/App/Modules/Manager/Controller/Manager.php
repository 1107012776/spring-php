<?php

namespace App\Manager\Controller;


use App\Model\ServerManagerModel;
use App\Rpc\WebSocketPushRpc;
use SpringPHP\Core\SocketController;
use SpringPHP\WebSocket\WebSocketClient;


class Manager extends SocketController
{
    public function push()
    {
        $data = [
            'code' => $this->request->post('code'),
            'user_id' => $this->request->post('user_id'),
            'content' => $this->request->post('content'),
        ];
        $model = new ServerManagerModel();
        $list = $model->findAll();
        foreach ($list as $val){
            $rpc = new WebSocketPushRpc(WebSocketClient::PROTOCOL_WS,$val['host'],$val['port']);
            $res = $rpc->push($data);
        }
        return ['code' => 200];
    }

    public function test()
    {
        $model = new ServerManagerModel();
        return [
            'zhi' => $model->findAll()
        ];
    }

    public function register()
    {
        $model = new ServerManagerModel();
        $info = $model->where([
            'host' => $this->request->post('host'),
            'port' => $this->request->post('port'),
        ])->find();
        if(empty($info)){
            $res = $model->insert([
                'host' => $this->request->post('host'),
                'port' => $this->request->post('port'),
                'uri' =>  $this->request->post('uri'),
                'protocol' =>  $this->request->post('protocol'),
            ]);
        }
        return ['code' => 200];
    }


}