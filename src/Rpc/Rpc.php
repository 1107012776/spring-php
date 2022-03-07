<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Rpc;

use Swoole\Coroutine\Client;

abstract class Rpc
{
    protected $apiBind = [];
    protected $port = 8080;
    protected $ip = '0.0.0.0';
    /**
     * @var Client
     */
    protected $client;

    public function __construct($timeOut = 3)
    {
        $this->connect($timeOut);
    }

    public function connect($timeOut = 3)
    {
        //https://www.kancloud.cn/oydm360782/swoole/44317
        $this->client = new Client(SWOOLE_SOCK_TCP);
        $this->client->set(
            [
                /*           'open_length_check'     => 1,
                           'package_length_type'   => 'N',
                           'package_length_offset' => 0,       //第N个字节是包长度的值
                           'package_body_offset'   => 4,       //第几个字节开始计算长度*/
                'package_max_length' => 1024 * 1024 * 20  //协议最大长度
            ]
        );
        $this->client->connect($this->ip, $this->port, $timeOut);
    }


    public function __call($name, $arguments)
    {
        $n = $this->send(json_encode(['jsonrpc' => '2.0', 'method' => $this->apiBind[$name], 'params' => isset($arguments[0]) ? $arguments[0] : [], 'id' => uniqid(time())], JSON_UNESCAPED_UNICODE));
        if (empty($n)) {
            return false;
        }
        $data = $this->recv();
        $res = json_decode($data, true);
        if (isset($res['result'])) {
            return $res['result'];
        }
        if (isset($res['error'])) {
            return $res['error'];
        }
        return $data;
    }

    public function isConnected()
    {
        return $this->client->isConnected();
    }

    public function send(string $rawData)
    {
        if ($this->client->isConnected()) {
            return $this->client->send($rawData);
        } else {
            return false;
        }
    }


    public function recv(float $timeout = 10)
    {
        if ($this->client->isConnected()) {
            $ret = $this->client->recv($timeout);
            if (!empty($ret)) {
                return $ret;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    function __destruct()
    {
        if ($this->client->isConnected()) {
            $this->client->close();
        }
    }
}