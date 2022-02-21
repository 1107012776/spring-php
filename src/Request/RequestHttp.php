<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Request;

use SpringPHP\Inter\RequestInter;

class RequestHttp implements RequestInter
{
    /**
     * https://wiki.swoole.com/#/http_server?id=httprequest
     * @var \Swoole\Http\Request
     */
    protected $request;
    /**
     * @var \Swoole\Server $serv
     */
    protected $serv = null;
    /**
     * @var \Swoole\Process $process
     */
    protected $process = null;

    protected $config = [];

    protected $params = [];

    public function __construct(
        \Swoole\Http\Request $request,
        \Swoole\Server $serv = null,
        \Swoole\Process $process = null,
        $config
    )
    {
        $this->request = $request;
        $this->serv = $serv;
        $this->process = $process;
        $this->config = $config;
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

    public function server()
    {
        return $this->request->server;
    }

    /**
     * @return \Swoole\Server
     */
    public function managerServer()
    {
        return $this->serv;
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

    public function method()
    {
        return $this->request->server['request_method'];
    }
}