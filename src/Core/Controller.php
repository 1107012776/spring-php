<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */
namespace SpringPHP\Core;

use SpringPHP\Inter\RequestInter;
use SpringPHP\Response\SocketResponse;

abstract class Controller
{
    /**
     * @var RequestInter
     */
    protected $request;
    /**
     * @var \SpringPHP\Inter\ResponseInter
     */
    protected $response;


    public function init(RequestInter $request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function beforeAction($action = '')
    {
        return true;
    }

    protected function responseCode($code = 200)
    {
        if (empty($this->response)) {
            return false;
        }
        if (get_class($this->response) == \Swoole\Http\Response::class) {
            $this->response->setStatusCode($code);
        }
        if (get_class($this->response) == SocketResponse::class) {
            $this->response->setStatusCode($code);
        }
    }

    protected function setHeader($key, $value, $ucwords = null)
    {
        if (empty($this->response)) {
            return false;
        }
        /**
         * @var \Swoole\Http\Response $response
         */
        $response = $this->response;
        if (get_class($response) == \Swoole\Http\Response::class) {
            $response->setHeader($key, $value, $ucwords);
        }
        return true;
    }

    protected function header($key, $value, $ucwords = null)
    {
        if (empty($this->response)) {
            return false;
        }
        /**
         * @var \Swoole\Http\Response $response
         */
        $response = $this->response;
        if (get_class($response) == \Swoole\Http\Response::class) {
            $response->header($key, $value, $ucwords);
        }
        return true;
    }

    protected function redirect($location, $http_code = null)
    {
        if (empty($this->response)) {
            return false;
        }
        /**
         * @var \Swoole\Http\Response $response
         */
        $response = $this->response;
        if (get_class($response) == \Swoole\Http\Response::class) {
            $response->redirect($location, $http_code);
        }
        return true;
    }
}