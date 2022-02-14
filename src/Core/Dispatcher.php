<?php

namespace SpringPHP\Core;

use SpringPHP\Request\RequestHttp;

class Dispatcher
{
    protected $queryString = '';
    protected $routing = '';
    protected $controller = '';
    protected $action = '';
    protected $request;
    /**
     * @var \Swoole\Http\Response
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
     */
    public static function init($request, $response)
    {
        $dis = static::createDispatcher();
        $dis->request = $request;
        $dis->response = $response;
        if ($request instanceof RequestHttp) {
            $dis->http($request);
        }
        $dis->bootstrap();
    }

    protected function http(RequestHttp $request)
    {
        $uri = $request->getUri();
        $arr = explode('?', $uri);
        $this->routing = $arr[0];
        $this->queryString = isset($arr[1]) ? $arr[1] : '';
    }

    public function bootstrap()
    {
        $arr = explode('/', $this->routing);
        $controller = $this->controller = !empty($arr[1]) ? $arr[1] : 'Index';
        $action = $this->action = !empty($arr[2]) ? $arr[2] : 'index';
        $fix = '\App\Controller\\';
        $this->response->setHeader('Content-Type', 'text/html;charset=UTF-8');
        if (!empty($controller) && !empty($action)) {
            $controllerClass = $fix . $controller;
            class_exists($controllerClass) && $obj = new $controllerClass();
            if (empty($obj) || !method_exists($obj, $action)) {
                $this->response->setStatusCode(404);
                $errorPageArr = SpringContext::$app->getConfig('error_page');
                if (!empty($errorPageArr[0]) && class_exists($errorPageArr[0])) {
                    $controllerClass = $errorPageArr[0];
                    $obj = new $controllerClass();
                    $action = isset($errorPageArr[1]) ? $errorPageArr[1] : '';
                    if (empty($action) || !method_exists($obj, $action)) {
                        echo '404';
                        return;
                    }
                } else {
                    echo '404';
                    return;
                }
            }
            /**
             * @var Controller $obj
             */
            $obj->init($this->request, $this->response);
            $response = $obj->$action();
            if (is_string($response)) {
                echo $response;
            } elseif (is_array($response)) {
                $this->response->setHeader('Content-Type', 'application/json;charset=UTF-8');
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }
}