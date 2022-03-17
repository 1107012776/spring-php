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
use SpringPHP\Inter\RequestInter;

class RequestWebSocket implements RequestInter
{
    protected $request;
    protected $process;

    protected $params = [];
    protected $data = [];

    public function __construct(
        $frame,
        \Swoole\Process $process = null
    )
    {
        $this->request = $frame;

        $this->process = $process;

        $this->data = json_decode($frame->data, true);
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
        return ManagerServer::getInstance()->getServerConfig('module_name', '');
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

}