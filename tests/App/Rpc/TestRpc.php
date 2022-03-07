<?php

namespace App\Rpc;


use SpringPHP\Rpc\Rpc;

/**
 * Class TestRpc
 * @package App\Rpc
 * @method Index_index($params)
 * @method Index_hello($params)
 */
class TestRpc extends Rpc
{
    protected $apiBind = [
        'Index_index' => '/Index/index',
        'Index_hello' => '/Index/hello'
    ];
    protected $port = 8497;
    protected $ip = '0.0.0.0';
}