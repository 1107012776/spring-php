<?php

namespace SpringPHP\Server;

use SpringPHP\Core\Crontab;
use SpringPHP\Core\SpringContext;
use SpringPHP\Template\Render;

class Server
{
    const SERVER_HTTP = 1;  //http
    const SERVER_WEBSOCKET = 2;  //webSocket
    const SERVER_SOCKET = 3;  //普通tcp socket
    /**
     * @var \Swoole\Server
     */
    protected $serv;
    protected $config;
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

    public function renderInit($port, $config){
        Render::getInstance()->attachServer($this->serv, $port, $config);
        Crontab::getInstance()->attachServer($this->serv, $config);
    }


    /**
     *
     * 每个worker启动的时候
     * @param \Swoole\Server $serv
     */
    public function onWorkerStart(\Swoole\Server $serv, $worker_id)
    {
        Server::workerStart();
        if ($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("spring-php.task.{$worker_id} pid=" . getmypid());
        } else {
            swoole_set_process_name("spring-php.worker.{$worker_id} listen:" . $this->host . ':' . $this->port);
        }
        if($worker_id == 0){ //重启RenderWorker
            \Swoole\Coroutine::create(function () {
                Render::getInstance()->restartWorker();
            });
        }
    }

    public static function start($config = [])
    {
        return new static($config);
    }
}