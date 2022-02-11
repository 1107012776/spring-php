<?php

namespace SpringPHP\Server;

use SpringPHP\Core\SpringContext;

class Server
{
    const SERVER_HTTP = 1;

    public static function onWorkerStart()
    {
        SpringContext::resetConfig();
        \SpringPHP\Core\SimpleAutoload::init();
        \SpringPHP\Core\SimpleAutoload::add([
            'App' => SPRINGPHP_ROOT . '/App'
        ]);
    }
}