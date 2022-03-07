<?php

namespace App\Rpc;


use SpringPHP\Rpc\Rpc;

/**
 * Class WebSocketManagerRpc
 * @package App\Rpc
 * @method push($params)
 * @method register($params)
 */
class ManagerRpc extends Rpc
{
    protected $apiBind = [
        'push' => '/Manager/push',
        'register' => '/Manager/register',
    ];
    protected $port = 8498;
    protected $ip = '0.0.0.0';
}