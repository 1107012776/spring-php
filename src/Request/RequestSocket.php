<?php

namespace SpringPHP\Request;

use SpringPHP\Inter\RequestInter;

/**
 * {"uri":"/Index/index","content":{"name":"213123123"}}
 * Class RequestSocket
 * @package SpringPHP\Request
 */
class RequestSocket implements RequestInter
{
    protected $serv;
    protected $process;
    protected $config = [];

    protected $params = [];
    protected $data = [];

    public function __construct(
        $data,
        \Swoole\Server $serv = null,
        \Swoole\Process $process = null,
        $config
    )
    {
        $this->serv = $serv;
        $this->process = $process;
        $this->config = $config;
        $this->data = json_decode($data, true);
    }

    public function getUri()
    {
        return $this->data['uri'];
    }

    public function managerServer()
    {
        return $this->serv;
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
        return $this->config;
    }

    public function getModuleName()
    {
        return empty($this->config['module_name']) ? '' : $this->config['module_name'];
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