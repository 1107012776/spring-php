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

class RequestHttp implements RequestInter
{
    /**
     * https://wiki.swoole.com/#/http_server?id=httprequest
     * @var \Swoole\Http\Request
     */
    protected $request;
    protected $fd;

    /**
     * @var \Swoole\Process $process
     */
    protected $process = null;


    protected $params = [];

    public function __construct(
        \Swoole\Http\Request $request,
        \Swoole\Process $process = null
    )
    {
        $this->request = $request;
        $this->fd = $request->fd;
        $this->process = $process;

    }

    /**
     * @return bool
     */
    public function isJsonrpc(): bool
    {
        return false;
    }


    public function get($key, $default = '')
    {
        return !empty($this->request->get[$key]) ? $this->request->get[$key] : $default;
    }

    public function post($key, $default = '')
    {
        return !empty($this->request->post[$key]) ? $this->request->post[$key] : $default;
    }

    public function params($key, $default = '')
    {
        return !empty($this->params[$key]) ? $this->params[$key] : $default;
    }

    public function setParams($params = [])
    {
        return $this->params = $params;
    }

    public function addParams($params = [])
    {
        return $this->params = array_merge($this->params, $params);
    }

    public function getContent()
    {
        return $this->request->getData();
    }

    public function rawContent()
    {
        return $this->request->rawContent();
    }

    public function getData()
    {
        return $this->request->getData();
    }


    public function files()
    {
        return $this->request->files;
    }

    public function tmpfiles()
    {
        return $this->request->tmpfiles;
    }


    public function getUri()
    {
        return $this->request->server['request_uri'];
    }

    public function header()
    {
        return $this->request->header;
    }

    public function getHost()
    {
        return isset($this->request->header['host']) ? $this->request->header['host'] : '';
    }

    public function server()
    {
        return $this->request->server;
    }

    /**
     * @return \Swoole\Server
     */
    public function managerServer()
    {
        return ManagerServer::getInstance()->getServer();
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

    public function method()
    {
        return $this->request->server['request_method'];
    }

    public function cookie($key = '', $default = '')
    {
        if (!empty($key)) {
            return isset($this->request->cookie[$key]) ? $this->request->cookie[$key] : $default;
        }
        return $this->request->cookie;
    }

    public function getClientInfo()
    {
        $client_info = $this->managerServer()->getClientInfo($this->fd);
        return $client_info;
    }

    public function getClientServer()
    {
        return $this->request->server;
    }

    public function getClientIp()
    {
        // 获取客户端连接信息
        $client_info = $this->request->server->getClientInfo($this->fd);
//        return [$client_info,$this->request->server,$this->header()];
        if (empty($client_info['remote_ip'])) {
            return false;
        }
        //头部里面的 "x-real-ip":"120.36.152.6","x-forwarded-for":"120.36.152.6"
        // 获取客户端 IP 地址
        $client_ip = $client_info['remote_ip'];
        return $client_ip;
    }


}