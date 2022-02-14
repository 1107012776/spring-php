<?php

namespace SpringPHP\Inter;

interface RequestInter
{
    public function getUri();

    /**
     * @return \Swoole\Server
     */
    public function getServer();
}