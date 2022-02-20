<?php

namespace SpringPHP\Core;

use SpringPHP\Component\SimpleAutoload;
use SpringPHP\Inter\RequestInter;
use SpringPHP\Request\RequestHttp;
use SpringPHP\Request\RequestWebSocket;
use SpringPHP\Request\RequestSocket;
use SpringPHP\Response\SocketResponse;
use SpringPHP\Route\Router;
use Swoole\Http\Response;

class Dispatcher
{
    protected $queryString = '';
    protected $routing = '';
    protected $controller = '';
    protected $action = '';
    protected $request;
    protected $module_name = '';
    /**
     * @var \SpringPHP\Inter\ResponseInter
     */
    protected $response;

    protected function __construct()
    {

    }

    public static function createDispatcher()
    {
        return new static();
    }


    /**
     * 初始化
     * @param $request
     * @param $response
     * @return string
     */
    public static function init($request, $response)
    {
        $dis = static::createDispatcher();
        $dis->request = $request;
        $dis->response = $response;
        if ($request instanceof RequestHttp) {
            $dis->http($request);
            return $dis->bootstrap();
        }
        if ($request instanceof RequestWebSocket) {
            $dis->http($request);
            return $dis->bootstrap();
        }

        if ($request instanceof RequestSocket) {
            $dis->http($request);
            return $dis->bootstrap();
        }
        return '';
    }


    protected function http(RequestInter $request)
    {
        $uri = $request->getUri();
        $arr = explode('?', $uri);
        /**
         * @var Router $router
         */
        $router = SpringContext::config('router');
        if (!empty($router)) {
            if ($resRoute = $router->match($arr[0], $request->method())) {
                $params = $resRoute->getParams();
                $request->setParams($params);
                $this->routing = $resRoute->getStorage();
            }
        }
        if (empty($this->routing)) {
            $this->routing = $arr[0];
        }
        $config = $request->getConfig();
        $this->module_name = empty($config['module_name']) ? '' : $config['module_name'];
        $this->queryString = isset($arr[1]) ? $arr[1] : '';
    }


    public function bootstrap()
    {
        if (!empty($this->response) && get_class($this->response) == Response::class) {
            $this->response->setHeader('Content-Type', 'text/html;charset=UTF-8');
        }
        if (is_callable($this->routing)) {
            $routingCallBack = $this->routing;
            return $routingCallBack($this->request, $this->response);
        }
        $arr = explode('/', $this->routing);
        $controller = $this->controller = !empty($arr[1]) ? $arr[1] : 'Index';
        $action = $this->action = !empty($arr[2]) ? $arr[2] : 'index';
        $fix = '\App\Controller\\';
        if (!empty($this->module_name)) {
            SimpleAutoload::add([
                'App\\' . $this->module_name => SPRINGPHP_ROOT . '/App/Modules/' . $this->module_name
            ]);
            $fix = 'App\\' . $this->module_name . '\\Controller\\';
        }
        if (!empty($controller) && !empty($action)) {
            $controllerClass = $fix . $controller;
            class_exists($controllerClass) && $obj = new $controllerClass();
            if (empty($obj) || !method_exists($obj, $action)) {
                if (!empty($this->response) && get_class($this->response) == Response::class) {
                    $this->response->setStatusCode(404);
                } elseif (!empty($this->response) && $this->response instanceof SocketResponse) {
                    $response = ['code' => 404];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                $errorPageArr = SpringContext::$app->getConfig('error_page');
                if (!empty($errorPageArr[0]) && class_exists($errorPageArr[0])) {
                    $controllerClass = $errorPageArr[0];
                    /**
                     * @var Controller $obj
                     */
                    $obj = new $controllerClass();
                    $obj->init($this->request, $this->response);
                    if (!$obj->beforeAction($action)) {
                        if (!empty($this->response) && get_class($this->response) == Response::class) {
                            $this->response->setStatusCode(403);
                        } elseif (!empty($this->response) && $this->response instanceof SocketResponse) {
                            $response = ['code' => 403];
                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                        }
                    }
                    $action = isset($errorPageArr[1]) ? $errorPageArr[1] : '';
                    if (empty($action) || !method_exists($obj, $action)) {
                        if (!empty($this->response) && $this->response instanceof SocketResponse) {
                            $response = ['code' => 404];
                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                        }
                        return '404';
                    }
                } else {
                    if (!empty($this->response) && $this->response instanceof SocketResponse) {
                        $response = ['code' => 404];
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    return '404';
                }
            }
            /**
             * @var Controller $obj
             */
            $obj->init($this->request, $this->response);
            if (!$obj->beforeAction($action)) {
                if (!empty($this->response) && get_class($this->response) == Response::class) {
                    $this->response->setStatusCode(403);
                } elseif (!empty($this->response) && $this->response instanceof SocketResponse) {
                    $response = ['code' => 403];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                return '';
            }
            $response = $obj->$action();
            if (is_string($response)) {
                return $response;
            } elseif (is_array($response)) {
                if (!empty($this->response) && $this->response instanceof SocketResponse) {
                    $WebSocketResponse = $this->response;
                    /**
                     * @var SocketResponse $WebSocketResponse
                     */
                    empty($response['code']) && $response['code'] = $WebSocketResponse->getHttpCode();
                } elseif (!empty($this->response) && get_class($this->response) == Response::class) {
                    $this->response->setHeader('Content-Type', 'application/json;charset=UTF-8');
                }
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
        return '';
    }
}