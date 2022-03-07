<?php

namespace App\Rpc;


use SpringPHP\Rpc\WebSocketRpc;
use SpringPHP\WebSocket\WebSocketClient;

/**
 * Class WebSocketTestRpc
 * @package App\Rpc
 * @method Index_index($params)
 * @method Index_websocket($params)
 */
class WebSocketTestRpc extends WebSocketRpc
{
    protected $apiBind = [
        'Index_index' => '/Index/index',
        'Index_websocket' => '/Index/websocket',
    ];
    protected $port = 8397;
    protected $ws = WebSocketClient::PROTOCOL_WS;
    protected $ip = '0.0.0.0';
}