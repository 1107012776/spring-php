<?php

namespace SpringPHP\Inter;
interface SessionInter
{
    public function setSessionId($id);

    public function getSessionId();

    public function start();

    public function read();

    public function write();

    public function close();

    public function remove($key);

    public function get($key, $default = '');

    public function set($key, $value);

    public function end();

    public function isOpen();

    public function setDomain($domain);

    public function getDomain();

    public function setMaxAge($time);

    public function getMaxAge();

    public function setPath($path);

    public function getPath();

    public function gc($timeout);
}