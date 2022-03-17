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
use SpringPHP\Request\RequestHttp;
use SpringPHP\Session\FileSession;
use SpringPHP\Session\Session;

abstract class HttpController extends Controller
{
    public function init(RequestInter $request, $response)
    {
        parent::init($request, $response);
        if ($request instanceof RequestHttp) {
            $open = ManagerServer::getInstance()->getServerConfig('session.open', false);
            if ($open) {
                $sessionName = ManagerServer::getInstance()->getServerConfig('session.name', 'SpringPHPSession');
                Session::getInstance()->start(new FileSession($request->cookie($sessionName)));
            }
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