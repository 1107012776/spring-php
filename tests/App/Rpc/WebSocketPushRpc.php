<?php

namespace App\Rpc;


use SpringPHP\Rpc\WebSocketRpc;
use SpringPHP\WebSocket\WebSocketClient;

/**
 * Class WebSocketPushRpc
 * @package App\Rpc
 * @method push($params)
 */
class WebSocketPushRpc extends WebSocketRpc
{
    protected $apiBind = [
        'push' => '/Index/publish',
    ];
    protected $port = 8397;
    protected $ws = WebSocketClient::PROTOCOL_WS;
    protected $ip = '0.0.0.0';

    public function __construct($ws, $ip, $port, $connectTimeout = 1.0, $rwTimeout = 30)
    {
        $this->ws = $ws;
        $this->ip = $ip;
        $this->port = $port;
        parent::__construct($connectTimeout, $rwTimeout);
    }
}