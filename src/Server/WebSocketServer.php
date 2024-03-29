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
                'http_compression' => $this->getSettingsConfig('settings.http_compression', true), // 针对 Swoole\Http\Response 对象的配置，启用压缩。默认为开启。
            ]
        );


        //监听WebSocket连接打开事件
        $ws->on('Open', function (\Swoole\Server $ws, \Swoole\Http\Request $request) {
            $this->fds[(int)$request->fd] = $request->fd;
            // 获取客户端 IP 地址
            $client_ip = $request->header['x-real-ip'] ?? $request->server['remote_addr'];
            if (!isset($ws->client_ips) || empty($ws->client_ips)) {
                $ws->client_ips = [];
                $ws->client_ips[(int)$request->fd] = $client_ip;
            } else {
                $ws->client_ips[(int)$request->fd] = $client_ip;
            }
            SpringContext::$app->set('fd', $request->fd);
            SpringContext::$app->set('client_ip', isset($ws->client_ips[(int)$request->fd]) ? $ws->client_ips[(int)$request->fd] : '127.0.0.1');
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
                $result = Dispatcher::init(new RequestWebSocket($frame, $this->swoole_process, $ws), new SocketResponse());
                $ws->push($frame->fd, $result);
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
                $ws->push($frame->fd, '');
            }
        });

        //监听WebSocket连接关闭事件
        $ws->on('Close', function ($ws, $fd) {
            SpringContext::$app->set('fd', $fd);
            SpringContext::$app->set('client_ip', isset($ws->client_ips[(int)$fd]) ? $ws->client_ips[(int)$fd] : '127.0.0.1');
            if ($this->getSettingsConfig('settings.debug', false) == true) {
                echo "client-{$fd} is closed" . " client_ip=" . SpringContext::$app->get('client_ip') . PHP_EOL;
            }
            $event = SpringContext::config('servers.' . $this->config['index'] . '.event_close', null);
            try {
                if (is_callable($event)) {
                    $event($ws, $fd);
                }
            } catch (\Exception $e) {
                echo $e->getTraceAsString() . PHP_EOL;
            }
            unset($this->fds[(int)$fd]);
            if (isset($ws->client_ips[(int)$fd])) {
                unset($ws->client_ips[(int)$fd]);
            }
            if (empty($this->startFdsTimer)) {
                $this->startFdsTimer = true;
                \Swoole\Timer::after(10 * 1000, function () use ($ws) {
                    if ($this->getSettingsConfig('settings.debug', false) == true) {
                        echo 'fds and client_ips = ' . var_export([$this->fds, $ws->client_ips], true) . __CLASS__ . PHP_EOL;
                    }
                    try {
                        foreach ($this->fds as $fd) {
                            if (!$ws->isEstablished($fd)) {
                                if ($this->getSettingsConfig('settings.debug', false) == true) {
                                    echo 'unset fd ' . __CLASS__ . PHP_EOL;
                                }
                                unset($this->fds[(int)$fd]);
                                if (isset($ws->client_ips) && isset($ws->client_ips[(int)$fd])) {
                                    if ($this->getSettingsConfig('settings.debug', false) == true) {
                                        echo 'unset client_ip ' . __CLASS__ . PHP_EOL;
                                    }
                                    unset($ws->client_ips[(int)$fd]);
                                }
                            }
                        }
                    } catch (\Exception $e) {

                    }
                    $this->startFdsTimer = false;
                });
            }
        });

        $this->init($this->port, $config);

        $ws->start();
    }
}