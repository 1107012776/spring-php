<?php

namespace SpringPHP\Server;
use SpringPHP\Core\Dispatcher;
use SpringPHP\Inter\ServerInter;
use SpringPHP\Request\RequestSocket;
use SpringPHP\Response\SocketResponse;

/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 12:30
 */
class TcpSocketServer  extends Server implements ServerInter
{
    public $port;
    public $host;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $host = $this->host = $config['host'];
        $port = $this->port = $config['port'];
        $this->serv = $server = new \Swoole\Server($host, $port);

         //监听连接进入事件
        $server->on('Connect', function ($server, $fd) {
            echo "Client: Connect.\n";
        });


       //监听数据接收事件
        $server->on('Receive', function (\Swoole\Server $server, $fd, $reactor_id, $data) {
            if(trim($data) == 'quit'){
                $server->send($fd, json_encode([
                    'code' => 200,
                    'msg' => 'quit'
                ],JSON_UNESCAPED_UNICODE));
                $server->close($fd);
                return;
            }
            try {
                $result = Dispatcher::init(new RequestSocket($data, $server , $this->swoole_process, $this->config), new SocketResponse());
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
            }
            $server->send($fd, $result);
        });


        //监听连接关闭事件
        $server->on('Close', function ($server, $fd) {
            echo "Client: Close.\n";
        });

        $server->on('start', function ($serv) {
            swoole_set_process_name('spring-php.Manager');
        });


        $server->on('workerStart', array($this, 'onWorkerStart'));


        $server->start();
    }
}