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

/**
 * https://www.jsonrpc.org/specification
 * {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}
 * {"jsonrpc": "2.0", "method": "/Index/index1", "params": [42, 23], "id": 1}
 * {"uri":"/Index/index","content":{"name":"213123123"}}
 * Class RequestSocket
 * @package SpringPHP\Request
 */
class RequestSocket implements RequestInter
{
    protected $process;
    protected $params = [];
    protected $data = [];
    protected $isJsonrpc = false;
    protected $fd = 0;


    public function __construct(
        $data,
        \Swoole\Process $process = null,
        $fd
    )
    {
        $this->process = $process;
        $arr = json_decode($data, true);
        $this->data = is_array($arr) ? $arr : $data;
        $this->fd = $fd;
        if (isset($this->data['jsonrpc']) && $this->data['jsonrpc'] == '2.0') {
            $this->isJsonrpc = true;
        }
    }

    public function getFd()
    {
        return $this->fd;
    }


    /**
     * @return bool
     */
    public function isJsonrpc(): bool
    {
        return $this->isJsonrpc;
    }

    public function setMergeData($data = '')
    {
        if ($this->isJsonrpc) {
            $this->data = array_merge($this->data['params'], $data);
        } else {
            $this->data = array_merge($this->data['content'], $data);
        }
    }

    public function getUri()
    {
        if ($this->isJsonrpc) {
            return $this->data['method'];
        }
        return $this->data['uri'];
    }


    public function getJsonRpcId()
    {
        if ($this->isJsonrpc) {
            return isset($this->data['id']) ? $this->data['id'] : 0;
        }
        return 0;
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


    public function get($key, $default = '')
    {
        if ($this->isJsonrpc) {
            return isset($this->data['params'][$key]) ? $this->data['params'][$key] : $default;
        }
        return isset($this->data['content'][$key]) ? $this->data['content'][$key] : $default;
    }

    public function post($key, $default = '')
    {
        if ($this->isJsonrpc) {
            return isset($this->data['params'][$key]) ? $this->data['params'][$key] : $default;
        }
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

    public function getConfig()
    {
        return ManagerServer::getInstance()->getServerConfig();
    }

    public function getModuleName()
    {
        return ManagerServer::getInstance()->getServerConfig('module_name', '');
    }


}