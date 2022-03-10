<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Rpc;


use SpringPHP\WebSocket\WebSocketClient;

/**
 * hook https://wiki.swoole.com/#/runtime?id=%e5%87%bd%e6%95%b0%e5%8e%9f%e5%9e%8b
 * Class WebSocketRpc
 * @package SpringPHP\Rpc
 */
abstract class WebSocketRpc
{
    protected $ws = WebSocketClient::PROTOCOL_WS;
    protected $apiBind = [];
    protected $port = '8397';
    protected $ip = '0.0.0.0';
    /**
     * @var WebSocketClient
     */
    protected $client;

    public function __construct($connectTimeout = 1.0, $rwTimeout = 30)
    {
        $this->connect($connectTimeout, $rwTimeout);
    }

    public function connect($connectTimeout = 1.0, $rwTimeout = 5.0)
    {
        try {
            $this->client = new WebSocketClient($this->ws . '://' . $this->ip . ':' . $this->port, $connectTimeout, $rwTimeout);
            $this->client->ping();
        } catch (\SpringPHP\WebSocket\ServerConnectException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function ping()
    {
        try {
            if (empty($this->client)) {
                return false;
            }
            return $this->client->ping();
        } catch (\Exception $e) {
            echo $e->__toString() . PHP_EOL;
            return false;
        }
    }

    public function close()
    {
        try {
            if (empty($this->client)) {
                return false;
            }
            $this->client->close();
        } catch (\Exception $e) {
            echo $e->__toString() . PHP_EOL;
            return false;
        }
    }

    public function recv()
    {
        try {
            if (empty($this->client)) {
                return false;
            }
            $frame = $this->client->recv();
            $playload = $frame->playload;
            return $playload;
        } catch (\Exception $e) {
            echo $e->__toString() . PHP_EOL;
            return false;
        }
    }

    public function __call($name, $arguments)
    {
        try {
            if (!$this->ping()) {
                return false;
            }
            $this->client->send(json_encode([
                'uri' => $this->apiBind[$name],
                'content' => isset($arguments[0]) ? $arguments[0] : []
            ], JSON_UNESCAPED_UNICODE));
            $frame = $this->client->recv();
            $playload = $frame->playload;
            if (isset($arguments[1]) && $arguments[1] == true) {
                $res = json_decode($playload, true);
                return is_array($res) ? $res : $playload;
            }
            return $playload;
        } catch (\Exception $e) {
            echo $e->__toString() . PHP_EOL;
            return false;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}

