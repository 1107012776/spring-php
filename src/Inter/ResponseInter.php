<?php

namespace SpringPHP\Inter;
/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 21:56
 */
interface ResponseInter
{
    public function setStatusCode($http_code = 200, $reason = null);

    public function setHeader($key, $value, $ucwords = null);

    public function header($key, $value, $ucwords = null);

    public function redirect($location, $http_code = null);
}