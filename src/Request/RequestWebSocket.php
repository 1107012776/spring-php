<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Request;

use SpringPHP\Core\ManagerServer;
use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\RequestInter;

class RequestWebSocket implements RequestInter
{
    protected $request;
    protected $process;
    protected $fd;
    /**
     * @var \Swoole\Server
     */
    protected $ws;

    protected $params = [];
    protected $data = [];

    public function __construct(
        \Swoole\Websocket\Frame $frame,
        \Swoole\Process $process = null,
        \Swoole\Server $ws = null
    )
    {
        $this->ws = $ws;
        $this->request = $frame;
        $this->fd = $frame->fd;
        $this->process = $process;
        $this->data = json_decode($frame->data, true);
        SpringContext::$app->set('fd', $this->fd);
        SpringContext::$app->set('client_ip', $this->getClientIp());
    }

    public function getFd()
    {
        return $this->request->fd;
    }

    /**
     * @return bool
     */
    public function isJsonrpc(): bool
    {
        return false;
    }

    public function getUri()
    {
        return $this->data['uri'];
    }

    /**
     * @return \Swoole\Server
     */
    public function managerServer()
    {
        return ManagerServer::getInstance()->getServer();
    }

    public function header()
    {
        return [];
    }

    public function server()
    {
        return [];
    }


    public function getProcess()
    {
        return $this->process;
    }

    public function getConfig()
    {
        return ManagerServer::getInstance()->getServerConfig();
    }

    public function getModuleName()
    {
        if (!empty(SpringContext::$app->get(RequestInter::class . '_module_name'))) {
            return SpringContext::$app->get(RequestInter::class . '_module_name');
        }
        return '';
    }

    public function get($key, $default = '')
    {
        return isset($this->data['content'][$key]) ? $this->data['content'][$key] : $default;
    }

    public function post($key, $default = '')
    {
        return isset($this->data['content'][$key]) ? $this->data['content'][$key] : $default;
    }

    public function params($key, $default = '')
    {
        return !empty($this->params[$key]) ? $this->params[$key] : $default;
    }

    public function setParams($params = [])
    {
        return $this->params = $params;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getContent()
    {
        return $this->data;
    }

    public function rawContent()
    {
        return $this->data;
    }

    public function files()
    {
        return [];
    }

    public function tmpfiles()
    {
        return [];
    }


    public function method()
    {
        return isset($this->data['method']) ? $this->data['method'] : 'GET';
    }

    public function getClientIp()
    {
        if (property_exists($this->ws, 'client_ips')) {
            if (!empty($this->ws->client_ips[$this->fd])) {
                return $this->ws->client_ips[$this->fd];
            }
        }
        // 获取客户端连接信息
        $client_info = $this->ws->getClientInfo($this->fd);
        if (empty($client_info['remote_ip'])) {
            return false;
        }
        // 获取客户端 IP 地址
        $client_ip = $client_info['remote_ip'];
        return $client_ip;
    }

}