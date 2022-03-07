<?php

namespace App\Timer;

use App\Model\ServerManagerModel;
use App\Rpc\WebSocketHealthyRpc;
use SpringPHP\Crontab\Timer;
use SpringPHP\Inter\TimerInter;
use SpringPHP\WebSocket\WebSocketClient;

class ManagerTimer extends Timer implements TimerInter
{
    public $ms = 1000;

    protected function crontabRule(): string
    {
        // 定义执行规则 根据Crontab来定义
        return '* * * * *';
    }

    protected function before()
    {

    }

    protected function exec()
    {
        $model = new ServerManagerModel();
        $list = $model->findAll();
        foreach ($list as $val){
           $rpc = new WebSocketHealthyRpc(WebSocketClient::PROTOCOL_WS,$val['host'],$val['port']);
           $heal = $rpc->healthy([], true);
           var_dump($heal);
           if(empty($heal)){
               $model->where(['id' => $val['id']])->delete();
           }
        }
    }

    protected function after()
    {

    }

    protected function onException(\Throwable $throwable, $arg = null)
    {

    }

}
