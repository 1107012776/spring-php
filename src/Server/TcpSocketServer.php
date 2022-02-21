<?php

namespace SpringPHP\Server;

use SpringPHP\Core\Dispatcher;
use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\ServerInter;
use SpringPHP\Inter\TaskInter;
use SpringPHP\Request\RequestSocket;
use SpringPHP\Response\SocketResponse;

/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 12:30
 */
class TcpSocketServer extends Server implements ServerInter
{
    public $port;
    public $host;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $host = $this->host = $config['host'];
        $port = $this->port = $config['port'];
        $this->serv = $server = new \Swoole\Server($host, $port);

        $server->set(
            [
                'worker_num' => SpringContext::config('settings.worker_num', 2),
                'daemonize' => false,
                'enable_coroutine' => SpringContext::config('settings.enable_coroutine', true),
                'max_request' => SpringContext::config('settings.max_request', 10000),
                'max_coroutine' => SpringContext::config('settings.max_coroutine', 100000),
                'open_http_protocol' => SpringContext::config('settings.open_http_protocol', true),
                'open_http2_protocol' => SpringContext::config('settings.open_http2_protocol', true),
                'socket_buffer_size' => SpringContext::config('settings.socket_buffer_size', 15 * 1024 * 1024),
                'buffer_output_size' => SpringContext::config('settings.buffer_output_size', 15 * 1024 * 1024),
                'package_max_length' => SpringContext::config('settings.package_max_length', 15 * 1024 * 1024),
                'open_tcp_nodelay' => SpringContext::config('settings.open_tcp_nodelay', true),
                'task_worker_num' => SpringContext::config('settings.task_worker_num', 0),
                'task_enable_coroutine' => SpringContext::config('settings.task_enable_coroutine', false),
                'enable_static_handler' => SpringContext::config('settings.enable_static_handler', false), //是否允许启动静态处理,如果存在会直接发送文件内容给客户端，不再触发onRequest回调
                'document_root' => SpringContext::config('settings.document_root', '')  //静态资源根目录
            ]
        );

        //监听连接进入事件
        $server->on('Connect', function ($server, $fd) {
            echo "Client: Connect.\n";
        });


        //监听数据接收事件
        $server->on('Receive', function (\Swoole\Server $server, $fd, $reactor_id, $data) {
            if (trim($data) == 'quit') {
                $server->send($fd, json_encode([
                    'code' => 200,
                    'msg' => 'quit'
                ], JSON_UNESCAPED_UNICODE));
                $server->close($fd);
                return;
            }
            try {
                $result = Dispatcher::init(new RequestSocket($data, $server, $this->swoole_process, $this->config), new SocketResponse());
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
            }
            $server->send($fd, $result);
        });


        //监听连接关闭事件
        $server->on('Close', function ($server, $fd) {
            echo "Client: Close.\n";
        });


        $this->init($this->port, $config);
        $server->start();
    }
}