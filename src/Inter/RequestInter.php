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

    //获取包含请求头和内容的
    public function getData();

    //获取包含请求头和内容的
    public function getContent();

    //获取input输入
    public function rawContent();

    public function files();

    public function tmpfiles();

    public function getProcess();

    public function getConfig();

    public function getModuleName();
}