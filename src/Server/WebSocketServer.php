<?php

namespace SpringPHP\Server;

use SpringPHP\Core\Dispatcher;
use SpringPHP\Inter\ServerInter;
use SpringPHP\Request\RequestWebSocket;
use SpringPHP\Response\SocketResponse;

/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 12:30
 */
class WebSocketServer extends Server implements ServerInter
{
    public $port;
    public $host;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $host = $this->host = $config['host'];
        $port = $this->port = $config['port'];
        $this->serv = $ws = new \Swoole\WebSocket\Server($host, $port);

        //监听WebSocket连接打开事件
        $ws->on('Open', function (\Swoole\Server $ws, \Swoole\Http\Request $request) {
            $ws->push($request->fd, json_encode([
                'code' => 200,
                'msg' => 'success'
            ], JSON_UNESCAPED_UNICODE));
        });

//监听WebSocket消息事件
        $ws->on('Message', function (\Swoole\Server $ws, \Swoole\Websocket\Frame $frame) {
            try {
                $result = Dispatcher::init(new RequestWebSocket($frame, $ws, $this->swoole_process, $this->config), new SocketResponse());
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
            }
            $ws->push($frame->fd, $result);
        });
        $ws->on('start', function ($serv) {
            swoole_set_process_name('spring-php.Manager');
        });

        $ws->on('workerStart', array($this, 'onWorkerStart'));

//监听WebSocket连接关闭事件
        $ws->on('Close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
    }
}