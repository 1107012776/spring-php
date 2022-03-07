<?php
namespace App\Event;

use SpringPHP\Core\ManagerServer;

class WebSocketOpenEvent{
    public static function start(\Swoole\Server $ws, \Swoole\Http\Request $request){
        $config = \SpringPHP\Core\ManagerServer::getInstance()->getServerConfig();
        $userSessionModel = new \App\Model\UserSessionModel();
        $userSessionModel->insert([
            'login_server' =>  ManagerServer::getInstance()->getUniquelyIdentifies().'_'.$config['host'].':'.$config['port'],
            'fd' => $request->fd
        ]);
    }
}
