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

    /**
     * 访问的控制器action名称
     * @var string
     */
    protected $action = '';

    public function init(RequestInter $request, $response)
    {
        $this->request = $request;
        if ($response instanceof SocketResponse) {
            $response->setRequest($request);
        }
        $this->response = $response;
    }

    public function beforeAction($action = '')
    {
        $this->action = $action;
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
        return true;
    }
}