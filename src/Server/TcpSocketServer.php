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
use SpringPHP\Core\ManagerServer;
use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\ServerInter;
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
    public $client_ips = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
        $host = $this->host = $config['host'];
        $port = $this->port = $config['port'];
        $this->serv = $server = new \Swoole\Server($host, $port);

        $server->set(
            [
                'worker_num' => $this->getSettingsConfig('settings.worker_num', 1),
                'daemonize' => false,
                'enable_coroutine' => $this->getSettingsConfig('settings.enable_coroutine', true),
                'max_request' => $this->getSettingsConfig('settings.max_request', 10000),
                'max_coroutine' => $this->getSettingsConfig('settings.max_coroutine', 100000),
                'socket_buffer_size' => $this->getSettingsConfig('settings.socket_buffer_size', 15 * 1024 * 1024),
                'buffer_output_size' => $this->getSettingsConfig('settings.buffer_output_size', 15 * 1024 * 1024),
                'package_max_length' => $this->getSettingsConfig('settings.package_max_length', 15 * 1024 * 1024),
                'open_tcp_nodelay' => $this->getSettingsConfig('settings.open_tcp_nodelay', true),
                'task_worker_num' => $this->getSettingsConfig('settings.task_worker_num', 0),
                'task_enable_coroutine' => $this->getSettingsConfig('settings.task_enable_coroutine', false),

            ]
        );
        //监听连接进入事件
        $server->on('Connect', function (\Swoole\Server $server, $fd) {
            $this->fds[(int)$fd] = $fd;
            // 获取客户端连接信息
            $client_info = $server->getClientInfo($fd);
            $this->client_ips[(int)$fd] = empty($client_info['remote_ip']) ? '127.0.0.1' : $client_info['remote_ip'];
            SpringContext::$app->set('fd', $fd);
            SpringContext::$app->set('client_ip', !empty($this->client_ips[(int)$fd]) ? $this->client_ips[(int)$fd] : '127.0.0.1');
            if ($this->getSettingsConfig('settings.debug', false) == true) {
                echo "Client: Connect." . (int)$fd . "\n";
            }
            $event = SpringContext::config('servers.' . $this->config['index'] . '.event_connect', null);
            if (is_callable($event)) {
                return $event($server, $fd);
            }
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
                $result = Dispatcher::init(new RequestSocket($data, $this->swoole_process, $fd), new SocketResponse());
                $server->send($fd, $result);
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
                $server->send($fd, '');
            }

        });


        //监听连接关闭事件
        $server->on('Close', function ($server, $fd) {
            SpringContext::$app->set('fd', $fd);
            SpringContext::$app->set('client_ip', !empty($this->client_ips[(int)$fd]) ? $this->client_ips[(int)$fd] : '127.0.0.1');
            unset($this->fds[(int)$fd]);
            unset($this->client_ips[(int)$fd]);
            if ($this->getSettingsConfig('settings.debug', false) == true) {
                echo "Client: Close." . (int)$fd . " client_ip=" . SpringContext::$app->get('client_ip') . PHP_EOL;
            }
            $event = SpringContext::config('servers.' . $this->config['index'] . '.event_close', null);
            if (is_callable($event)) {
                $event($server, $fd);
            }
            if (empty($this->startFdsTimer)) {
                $this->startFdsTimer = true;
                \Swoole\Timer::after(10 * 1000, function () use ($server) {
                    if ($this->getSettingsConfig('settings.debug', false) == true) {
                        echo 'fds = ' . var_export([$this->fds, $this->client_ips], true) . __CLASS__ . PHP_EOL;
                    }
                    try {
                        foreach ($this->fds as $fd) {
                            if (!$server->exist($fd)) {
                                if ($this->getSettingsConfig('settings.debug', false) == true) {
                                    echo 'unset fd ' . __CLASS__ . PHP_EOL;
                                }
                                unset($this->fds[(int)$fd]);
                                unset($this->client_ips[(int)$fd]);
                            }
                        }
                    } catch (\Exception $e) {

                    }
                    $this->startFdsTimer = false;
                });
            }
        });
        $this->init($this->port, $config);
        $server->start();
    }
}