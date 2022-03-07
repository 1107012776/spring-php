<?php

namespace App\Event;

use SpringPHP\Core\ManagerServer;

class WebSocketCloseEvent
{
    public static function start(\Swoole\Server $ws, $fd)
    {
        $config = \SpringPHP\Core\ManagerServer::getInstance()->getServerConfig();
        $userSessionModel = new \App\Model\UserSessionModel();
        $info = $userSessionModel->where([
            'login_server' => ManagerServer::getInstance()->getUniquelyIdentifies() . '_' . $config['host'] . ':' . $config['port'],
            'fd' => $fd
        ])->find();
        $userSessionModel->renew()->where([
            'login_server' => ManagerServer::getInstance()->getUniquelyIdentifies() . '_' . $config['host'] . ':' . $config['port'],
            'fd' => $fd
        ])->delete();
        if (!empty($info)) {
            $user = new \App\Model\UserModel();
            $user->where([
                'username' => $info['username']
            ])->update([
                'heart' => 0
            ]);
        }
    }
}
