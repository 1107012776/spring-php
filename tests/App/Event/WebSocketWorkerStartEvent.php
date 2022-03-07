<?php
namespace App\Event;

class WebSocketWorkerStartEvent{
    public static function start(){
        \Swoole\Timer::after(3000, function (){
            $config = \SpringPHP\Core\ManagerServer::getInstance()->getServerConfig();
            $rpc = new \App\Rpc\ManagerRpc();
            $res = $rpc->register([
                'host' => $config['host'],
                'port' => $config['port'],
                'protocol' => 'ws://',
                'uri' => '/Index/healthy',
            ]);
            var_dump($res);
        });
    }
}
