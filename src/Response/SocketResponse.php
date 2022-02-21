<?php

namespace SpringPHP\Response;
/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 21:22
 */
class SocketResponse
{
    protected $http_code = 200;
    protected $reason;

    public function setStatusCode($http_code = 200, $reason = null)
    {
        $this->http_code = $http_code;
        $this->reason = $reason;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->http_code;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

}