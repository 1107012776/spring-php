<?php

namespace SpringPHP\Server;

use SpringPHP\Core\SpringContext;

class Server
{
    const SERVER_HTTP = 1;

    public static function onWorkerStart()
    {
        SpringContext::resetConfig();
        \SpringPHP\Component\SimpleAutoload::init();
        \SpringPHP\Component\SimpleAutoload::add([
            'App' => SPRINGPHP_ROOT . '/App'
        ]);
    }
}