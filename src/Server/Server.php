<?php

namespace SpringPHP\Server;

use SpringPHP\Crontab\Crontab;
use SpringPHP\Template\Render;
use SpringPHP\Core\ManagerServer;
use SpringPHP\Core\SpringContext;


class Server
{
    const SERVER_HTTP = 1;  //http
    const SERVER_WEBSOCKET = 2;  //webSocket
    const SERVER_SOCKET = 3;  //普通tcp socket
    /**
     * @var \Swoole\Server
     */
    protected $serv;

    protected $config; //serverConfig
    /**
     * @var \Swoole\Process $swoole_process
     */
    protected $swoole_process;

    public function __construct($config = [])
    {
        $this->swoole_process = $config['process'];
        $this->config = $config;
    }

    public static function workerStart()
    {
        SpringContext::resetConfig();
        \SpringPHP\Component\SimpleAutoload::init();
        \SpringPHP\Component\SimpleAutoload::add([
            'App' => SPRINGPHP_ROOT . '/App'
        ]);
    }

    /**
     * 初始化赋值server启动之前
     * @param $port
     * @param $config
     */
    public function init($port, $config)
    {
        ManagerServer::getInstance()->setServerConfig($config);
        ManagerServer::getInstance()->setServer($this->serv);
        Render::getInstance()->attachServer($this->serv, $port, $config);
        Crontab::getInstance()->attachServer($this->serv, $config);
    }


    public static function start($config = [])
    {
        return new static($config);
    }
}