<?php
/**
 * https://blog.csdn.net/zhang197093/article/details/106873892/
 */

namespace SpringPHP\WebSocket;

/**
 * websocket数据帧
 * Class wsDataFrame
 * @package SpringPHP\WebSocket
 */
class WsDataFrame
{
    /**
     * @var int $opcode
     */
    public $opcode;

    /**
     * @var int $fin 标识数据包是否已结束
     */
    public $fin;

    /**
     * @var int $status 关闭时的状态码，如果有的话
     */
    public $status;

    /**
     * @var string 数据包携带的数据
     */
    public $playload;
}
