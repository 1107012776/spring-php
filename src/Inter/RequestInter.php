<?php

namespace SpringPHP\Inter;

interface RequestInter
{
    public function getUri();

    /**
     * @return \Swoole\Server
     */
    public function getServer();

    public function get($key, $default = '');

    public function post($key, $default = '');

    public function getContent();

    public function rawContent();

    public function files();

    public function tmpfiles();

    public function getProcess();

    public function getConfig();

    public function getModuleName();
}