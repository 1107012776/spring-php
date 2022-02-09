<?php

namespace SpringPHP\Inter;

interface ServerInter
{
    public static function start($host = '0.0.0.0', $port = 7999);
}