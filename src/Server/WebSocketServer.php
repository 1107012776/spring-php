<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Server;

use SpringPHP\Core\Dispatcher;
use SpringPHP\Core\SpringContext;
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

        $ws->set(
            [
                'heartbeat_check_interval' => $this->getSettingsConfig('settings.heartbeat_check_interval', 30),       //心跳检测 每隔多少秒，遍历一遍所有的连接
                'heartbeat_idle_time' => $this->getSettingsConfig('settings.heartbeat_idle_time', 65),           //心跳检测 最大闲置时间，超时触发close并关闭  默认为heartbeat_check_interval的2倍，两倍是容错机制，多一点是网络延迟的弥补
                'worker_num' => $this->getSettingsConfig('settings.worker_num', 1),
                'daemonize' => false,
                'enable_coroutine' => $this->getSettingsConfig('settings.enable_coroutine', true),
                'max_request' => $this->getSettingsConfig('settings.max_request', 10000),
                'max_coroutine' => $this->getSettingsConfig('settings.max_coroutine', 100000),
                'open_http_protocol' => $this->getSettingsConfig('settings.open_http_protocol', true),
                'open_http2_protocol' => $this->getSettingsConfig('settings.open_http2_protocol', true),
                'socket_buffer_size' => $this->getSettingsConfig('settings.socket_buffer_size', 15 * 1024 * 1024),
                'buffer_output_size' => $this->getSettingsConfig('settings.buffer_output_size', 15 * 1024 * 1024),
                'package_max_length' => $this->getSettingsConfig('settings.package_max_length', 15 * 1024 * 1024),
                'open_tcp_nodelay' => $this->getSettingsConfig('settings.open_tcp_nodelay', true),
                'task_worker_num' => $this->getSettingsConfig('settings.task_worker_num', 0),
                'task_enable_coroutine' => $this->getSettingsConfig('settings.task_enable_coroutine', false),
            ]
        );
        //监听WebSocket连接打开事件
        $ws->on('Open', function (\Swoole\Server $ws, \Swoole\Http\Request $request) {
            $this->fds[(int)$request->fd] = $request->fd;
            $ws->push($request->fd, json_encode([
                'code' => 200,
                'msg' => 'success'
            ], JSON_UNESCAPED_UNICODE));
            $event = SpringContext::config('servers.' . $this->config['index'] . '.event_open', null);
            if (is_callable($event)) {
                return $event($ws, $request);
            }
        });

//监听WebSocket消息事件
        $ws->on('Message', function (\Swoole\Server $ws, \Swoole\Websocket\Frame $frame) {
            try {
                $result = Dispatcher::init(new RequestWebSocket($frame, $this->swoole_process), new SocketResponse());
                $ws->push($frame->fd, $result);
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
                $ws->push($frame->fd, '');
            }
        });

//监听WebSocket连接关闭事件
        $ws->on('Close', function ($ws, $fd) {
            unset($this->fds[(int)$fd]);
            if ($this->getSettingsConfig('settings.debug', false) == true) {
                echo "client-{$fd} is closed\n";
            }
            $event = SpringContext::config('servers.' . $this->config['index'] . '.event_close', null);
            if (is_callable($event)) {
                return $event($ws, $fd);
            }
        });

        $this->init($this->port, $config);

        $ws->start();
    }
}